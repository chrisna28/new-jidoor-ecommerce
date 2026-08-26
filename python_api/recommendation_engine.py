import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.neighbors import NearestNeighbors
from sklearn.metrics import mean_absolute_error, mean_squared_error
from sklearn.model_selection import train_test_split
from scipy.sparse import csr_matrix
import time
import pymysql
import os
import random
import logging
from typing import List, Dict, Any

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

"""
JI-DOOR PURE COLLABORATIVE FILTERING ENGINE v5.3
=================================================
Mesin rekomendasi murni berbasis Collaborative Filtering:
1. User-Based CF: Merekomendasikan produk berdasarkan perilaku pengguna serupa.
2. Item-Based CF: Merekomendasikan produk berdasarkan kemiripan antar produk (Cosine Similarity).
3. Popular Fallback: Untuk Cold Start (user baru tanpa data interaksi).
"""


class RecommendationEngine:
    """
    Mesin inti untuk perhitungan kemiripan dan prediksi rekomendasi.
    
    Attributes:
        db_config (dict): Kredensial koneksi database MySQL.
        cache (dict): Penyimpanan sementara untuk matrix similarity dan pivot table.
        ttl (int): Time-To-Live cache (dalam detik). Default 60s.
    """
    def __init__(self, db_config: Dict[str, Any]):
        self.db_config = db_config
        self.cache = {
            "ratings": None,      # Dataframe rating gabungan (explicit + implicit)
            "user_sim": None,     # Matrix kemiripan antar user
            "item_sim": None,     # Matrix kemiripan antar produk
            "pivot": None,        # Pivot table User-Item
            "signature": None,    # Hash unik untuk mendeteksi perubahan DB
            "timestamp": 0        # Waktu terakhir cache diperbarui
        }
        self.ttl = 60

    # ==========================================================
    # DATABASE CONNECTION
    # ==========================================================

    def get_db_connection(self):
        """Membuat koneksi ke database MySQL (dengan fallback ke MAMP socket)."""
        config = self.db_config.copy()
        mamp_socket = "/Applications/MAMP/tmp/mysql/mysql.sock"
        if os.path.exists(mamp_socket):
            config["unix_socket"] = mamp_socket
            config.pop("host", None)
            config.pop("port", None)
        return pymysql.connect(**config)

    def get_db_signature(self) -> str:
        """Membuat signature unik dari state database untuk cache invalidation."""
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                queries = {
                    "r": "SELECT COUNT(*) as c, MAX(id) as m FROM ratings",
                    "o": "SELECT COUNT(*) as c, MAX(id) as m FROM orders",
                    "oi": "SELECT COUNT(*) as c, MAX(id) as m FROM order_items",
                    # order_tracking bertambah tiap perubahan status pesanan →
                    # perubahan status otomatis memicu recompute model
                    "ot": "SELECT COUNT(*) as c, MAX(id) as m FROM order_tracking",
                    "ct": "SELECT COUNT(*) as c, MAX(id) as m FROM cart",
                    "l": "SELECT COUNT(*) as c, MAX(id) as m FROM likes",
                    "v": "SELECT COUNT(*) as c, MAX(id) as m FROM product_views",
                }
                sig = ""
                for k, q in queries.items():
                    try:
                        cursor.execute(q)
                        res = cursor.fetchone()
                        sig += f"{k}{res['c']}-{res['m']}|"
                    except: sig += f"{k}0-0|"
                return sig
        finally:
            conn.close()

    # ==========================================================
    # DATA COLLECTION — Multi-Signal Rating Matrix
    # ==========================================================

    def fetch_enhanced_ratings(self, force: bool = False) -> pd.DataFrame:
        """
        Mengambil dan menggabungkan sinyal interaksi pengguna dari berbagai tabel DB.
        Pembobotan:
        - Explicit Rating → Bobot asli (1-5)
        - Purchase → Sinyal kepercayaan kuat (5.0)
        - Cart → Minat aktif (2.0)
        - Like → Minat pasif (2.5)
        - Product Views → Sinyal lemah (1.5)

        Menerapkan 'Time Decay' lalu MENGAKUMULASIKAN semua sinyal per user×item
        (bukan dedup prioritas tertinggi), sehingga setiap jenis sinyal dihitung.
        """
        current_sig = self.get_db_signature()
        if not force and self.cache["ratings"] is not None and current_sig == self.cache["signature"]:
            if (time.time() - self.cache["timestamp"]) < self.ttl:
                return self.cache["ratings"]

        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                # 1. Explicit Rating
                cursor.execute("SELECT user_id, product_id, CAST(rating AS FLOAT) as rating, created_at, 'explicit' as source FROM ratings")
                s1 = list(cursor.fetchall())
                
                # 2. Purchase (status 'delivered' = skema sekarang; 'completed' sudah tidak dipakai)
                cursor.execute("SELECT o.user_id, oi.product_id, 5.0 as rating, o.created_at, 'purchase' as source FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE o.status IN ('pending', 'paid', 'shipped', 'delivered')")
                s2 = list(cursor.fetchall())
                
                # 3. Cart
                cursor.execute("SELECT user_id, product_id, 2.0 as rating, created_at, 'cart' as source FROM cart")
                s3 = list(cursor.fetchall())
                
                # 4. Like
                cursor.execute("SELECT user_id, product_id, 2.5 as rating, created_at, 'like' as source FROM likes")
                s4 = list(cursor.fetchall())
                
                # 5. Product Views (Implicit) — guest (user_id NULL/0) dibuang agar pivot bersih.
                #    Cap v5.5: view berulang dihitung maks 3 per user×item (GROUP BY + LEAST)
                #    agar heavy viewer tidak mendominasi sinyal.
                cursor.execute("""
                    SELECT user_id, product_id, LEAST(COUNT(*), 3) * 1.5 as rating,
                           MAX(created_at) as created_at, 'view' as source
                    FROM product_views
                    WHERE user_id IS NOT NULL AND user_id > 0
                    GROUP BY user_id, product_id
                """)
                s5 = list(cursor.fetchall())
                
            df = pd.DataFrame(s1 + s2 + s3 + s4 + s5)
            if df.empty: return df
            
            # Convert to float to avoid Decimal * float error
            df['rating'] = df['rating'].astype(float)

            # Time Decay — Data lama memiliki pengaruh lebih kecil
            df['created_at'] = pd.to_datetime(df['created_at'])
            now = pd.Timestamp.now()
            df['days_old'] = (now - df['created_at']).dt.days
            
            def get_decay_factor(days):
                if days <= 7: return 1.0
                if days <= 30: return 0.8
                if days <= 90: return 0.5
                return 0.3
            
            df['factor'] = df['days_old'].apply(get_decay_factor)
            df['rating'] = df['rating'] * df['factor']

            # ============================================================
            # AKUMULASI MULTI-SINYAL (v5.5)
            # Sebelumnya dedup "prioritas tertinggi" membuang like/cart/view
            # saat tumpang tindih dengan rating/purchase → like tampak 0.
            # Sekarang: sinyal TERKUAT menjadi base afinitas, dan tiap jenis
            # sinyal TAMBAHAN menambah bonus konfirmasi (+0.3). Ini
            # mempertahankan skala rating 1-5 (purchase 5.0, like 2.5, dst.)
            # tanpa over-saturasi akibat penjumlahan murni.
            # ============================================================

            # Distribusi sumber MENTAH (untuk dashboard — sebelum agregasi)
            self.cache["raw_source_dist"] = df["source"].value_counts().to_dict()

            source_rank = {'explicit': 4, 'purchase': 3, 'like': 2, 'cart': 1, 'view': 0}
            df['rank'] = df['source'].map(source_rank)

            # Base = rating sinyal terkuat per user×item (rank tertinggi)
            base = (
                df.sort_values('rank', ascending=False)
                  .drop_duplicates(['user_id', 'product_id'])
                  [['user_id', 'product_id', 'rating', 'source']]
                  .set_index(['user_id', 'product_id'])
            )
            # Jumlah jenis sinyal berbeda + tanggal terbaru per user×item
            n_src = df.groupby(['user_id', 'product_id'])['source'].nunique().rename('n_src')
            latest = df.groupby(['user_id', 'product_id'])['created_at'].max()

            agg = base.join(n_src).join(latest)
            agg['rating'] = (agg['rating'] + 0.3 * (agg['n_src'] - 1)).clip(0.5, 5.0)
            agg = agg.reset_index()[['user_id', 'product_id', 'rating', 'source', 'created_at']]
            df = agg

            self.cache["ratings"] = df
            self.cache["signature"] = current_sig
            self.cache["timestamp"] = time.time()
            return df
        finally:
            conn.close()

    # ==========================================================
    # SIMILARITY COMPUTATION — Cosine Similarity
    # ==========================================================

    def compute_similarity(self, shrinkage_lambda: int = 3):
        """
        Menghitung matrix similarity dengan Conditional Mean-Centering dan
        Significance Shrinkage (v5.5, Herlocker et al.): sim' = sim · n/(n+λ).
        λ memperlemah pasangan yang hanya didukung sedikit user bersama.
        """
        df = self.fetch_enhanced_ratings()
        if df.empty: return
        self.cache["shrinkage_lambda"] = shrinkage_lambda
        
        # 1. Pivot Table (missing = NaN)
        pivot = df.pivot_table(index="user_id", columns="product_id", values="rating")
        
        # 2. Check Sparsity
        n_users, n_items = pivot.shape
        sparsity = 1.0 - (len(df) / (n_users * n_items))

        # Hint skala data untuk auto-tuning hyperparameter (v5.4)
        # agar mesin tetap masuk akal di data kecil DAN siap tumbuh ke data besar.
        self.cache["scale_hint"] = {
            "n_users": int(n_users),
            "n_items": int(n_items),
            "density": round(len(df) / (n_users * n_items), 4) if n_users * n_items else 0,
            "total_signals": int(len(df)),
        }
        
        # 3. Conditional Mean-Centering
        # Jika data sangat sparse (> 85%), Mean-Centering akan menghasilkan vektor nol (Red Flag)
        if sparsity < 0.85:
            user_mean = pivot.mean(axis=1)
            pivot_centered = pivot.sub(user_mean, axis=0).fillna(0)
        else:
            # Gunakan raw pivot (fillna 0)
            pivot_centered = pivot.fillna(0)
            
        pivot_raw = pivot.fillna(0)
        
        # 4. Matrix Similarity dengan Co-occurrence Penalty
        # Hitung co-occurrence matrix (berapa user yang berinteraksi dengan kedua produk)
        binary_pivot = (pivot_raw > 0).astype(int)
        co_matrix = np.dot(binary_pivot.T, binary_pivot)
        
        # Hitung Similarity menggunakan Cosine
        item_sim_matrix = cosine_similarity(pivot_centered.T)
        item_sim_matrix = np.nan_to_num(item_sim_matrix, nan=0.0)
        
        # --- SIGNIFICANCE SHRINKAGE (v5.5) ---
        # Kanonik: sim' = sim · n/(n+λ). Halus & berbasis bukti — pasangan dengan
        # 1 user bersama hanya dipertahankan ~25% (λ=3), dan mendekati penuh saat n besar.
        lam = shrinkage_lambda
        shrink = co_matrix / (co_matrix + lam)
        item_sim_matrix = item_sim_matrix * shrink
        
        # 5. User-User Similarity (Gunakan logika yang sama)
        user_sim_matrix = cosine_similarity(pivot_centered)
        user_sim_matrix = np.nan_to_num(user_sim_matrix, nan=0.0)
        
        # 6. Save to Cache
        self.cache["item_sim"] = pd.DataFrame(item_sim_matrix, index=pivot_centered.columns, columns=pivot_centered.columns)
        self.cache["user_sim"] = pd.DataFrame(user_sim_matrix, index=pivot_centered.index, columns=pivot_centered.index)
        self.cache["co_matrix"] = pd.DataFrame(co_matrix, index=pivot_centered.columns, columns=pivot_centered.columns)
        self.cache["pivot_centered"] = pivot_centered
        self.cache["pivot"] = pivot_raw
        
        # 7. Update KNN Model (Untuk User-Based recs)
        n_neighbors_user = min(16, n_users)
        knn_user = NearestNeighbors(n_neighbors=n_neighbors_user, metric='cosine', algorithm='brute')
        if pivot_centered.shape[0] > 0:
            # Minimal samples check — v5.5: cukup ≥2 user & ≥2 produk
            # agar pasangan tetangga tetap bermakna di data kecil.
            if pivot_centered.shape[0] >= 2 and pivot_centered.shape[1] >= 2:
                knn_user.fit(csr_matrix(pivot_centered.values))
                self.cache["knn_user"] = knn_user # Fix v5.2: Move inside if
            else:
                self.cache["knn_user"] = None
                logger.warning("Pivot matrix too small for User-KNN, falling back to popularity.")
        
        # Untuk item recs, kita bisa gunakan item_sim_matrix langsung atau fit KNN lagi
        n_neighbors_item = min(16, n_items)
        knn_item = NearestNeighbors(n_neighbors=n_neighbors_item, metric='precomputed', algorithm='brute')
        # Precomputed KNN menggunakan Jarak (1 - similarity)
        dist_matrix = np.clip(1.0 - item_sim_matrix, 0, 1)
        knn_item.fit(dist_matrix)
        self.cache["knn_item"] = knn_item

    # ==========================================================
    # CORE CF ALGORITHMS
    # ==========================================================

    def _get_user_recs(self, user_id: int, top_n: int) -> Dict[int, float]:
        """
        User-Based CF — formula kanonik (v5.5, sesuai literatur KNN-CF):
            pred(u,i) = mu_u + Σ s(u,v)·(r(v,i) − mu_v) / Σ|s(u,v)|
        Output = affinity 0..1: seberapa jauh prediksi di atas titik netral (3.0).
        """
        pivot = self.cache.get("pivot")
        pivot_centered = self.cache.get("pivot_centered")
        if pivot_centered is None or user_id not in pivot_centered.index: return {}
        
        knn_user = self.cache.get("knn_user")
        if not knn_user: return {}
        
        user_vector = pivot_centered.loc[[user_id]].values
        distances, indices = knn_user.kneighbors(user_vector)
        
        user_rated = pivot.loc[user_id]
        unrated = user_rated[user_rated == 0].index
        own_ratings = user_rated[user_rated > 0]
        mu_u = float(own_ratings.mean()) if len(own_ratings) else 0.0
        
        num: Dict[int, float] = {}
        den: Dict[int, float] = {}
        for i, neighbor_idx in enumerate(indices[0]):
            sim_score = 1 - distances[0][i]
            if sim_score <= 0.05: continue
            
            other_user = pivot_centered.index[neighbor_idx]
            if other_user == user_id: continue
            
            other_ratings = pivot.loc[other_user]
            other_centered = pivot_centered.loc[other_user]
            
            for prod_id in unrated:
                if other_ratings[prod_id] > 0:
                    num[prod_id] = num.get(prod_id, 0.0) + sim_score * float(other_centered[prod_id])
                    den[prod_id] = den.get(prod_id, 0.0) + abs(sim_score)
        
        # Prediksi ter-normalisasi denominasi → affinity (skala rating 1-5)
        scores: Dict[int, float] = {}
        for prod_id in num:
            if den[prod_id] < 1e-9: continue
            pred = mu_u + (num[prod_id] / den[prod_id])
            affinity = max(0.0, min(1.0, (pred - 3.0) / 2.0))
            if affinity > 0:
                scores[prod_id] = affinity
        
        return dict(sorted(scores.items(), key=lambda x: x[1], reverse=True))

    def _normalize_scores_global(self, u_scores: Dict[int, float], i_scores: Dict[int, float]):
        """
        Normalisasi PER-MODEL terpisah (v5.5): masing-masing model diskalakan ke 0-1
        dengan rentangnya sendiri, sehingga tidak ada model yang menenggelamkan
        model lain saat di-blend dengan alpha (praktik standar hybrid CF).
        """
        def scale_own(scores: Dict[int, float]) -> Dict[int, float]:
            if not scores:
                return {}
            vals = list(scores.values())
            mn, mx = min(vals), max(vals)
            if mx == mn:
                # Kandidat tunggal = keyakinan penuh; beberapa kandidat seri = titik tengah
                return {k: (1.0 if len(scores) == 1 else 0.5) for k in scores}
            return {k: (v - mn) / (mx - mn) for k, v in scores.items()}
        
        return scale_own(u_scores), scale_own(i_scores)

    def _normalize_scores(self, scores: Dict[int, float]) -> Dict[int, float]:
        """Backward compatibility for single score normalization"""
        if not scores: return {}
        min_val = min(scores.values())
        max_val = max(scores.values())
        if max_val == min_val: return {k: 1.0 for k in scores}
        return {k: (v - min_val) / (max_val - min_val) for k, v in scores.items()}

    def _get_item_recs(self, user_id: int, top_n: int) -> Dict[int, float]:
        """
        Item-Based CF — formula kanonik (v5.5):
            score(j) = Σ_{i ∈ profil} sim(i,j)·r(u,i) / Σ sim(i,j)
        dibatasi top-K tetangga per item profil (K adaptif, selaras Parameter Aktif).
        Output = affinity 0..1 di atas titik netral (3.0).
        """
        pivot = self.cache.get("pivot")
        item_sim_df = self.cache.get("item_sim")
        if item_sim_df is None or user_id not in pivot.index: return {}
        
        user_ratings = pivot.loc[user_id]
        interacted = user_ratings[user_ratings > 0].index
        unrated = user_ratings[user_ratings == 0].index
        
        hint = self.cache.get("scale_hint") or {}
        k_neighbors = max(min(16, int(hint.get("n_items", 16)) - 1), 1)
        
        num: Dict[int, float] = {}
        den: Dict[int, float] = {}
        for pid in interacted:
            if pid not in item_sim_df.index: continue
            r_ui = float(user_ratings[pid])
            
            # Top-K tetangga termirip (menggantikan cutoff 0.1 yang kaku)
            sim_items = (
                item_sim_df[pid]
                .drop(index=pid, errors="ignore")
                .sort_values(ascending=False)
                .head(k_neighbors)
            )
            
            for sim_pid, sim_score in sim_items.items():
                if sim_score <= 0: continue
                if sim_pid in unrated:
                    num[sim_pid] = num.get(sim_pid, 0.0) + sim_score * r_ui
                    den[sim_pid] = den.get(sim_pid, 0.0) + abs(sim_score)
        
        scores: Dict[int, float] = {}
        for prod_id in num:
            if den[prod_id] < 1e-9: continue
            affinity = max(0.0, min(1.0, (num[prod_id] / den[prod_id] - 3.0) / 2.0))
            if affinity > 0:
                scores[prod_id] = affinity
        
        return dict(sorted(scores.items(), key=lambda x: x[1], reverse=True))

    # ==========================================================
    # HYBRID RECOMMENDATION — Pure CF (User + Item + Popular)
    # ==========================================================

    def get_active_params(self) -> Dict[str, Any]:
        """
        Hyperparameter aktif hasil auto-tuning v5.4.
        Satu sumber kebenaran untuk engine DAN dashboard admin agar selalu konsisten.
        """
        hint = self.cache.get("scale_hint") or {}
        n_u = hint.get("n_users", 0)
        n_i = hint.get("n_items", 0)

        alpha = 0.6 if n_u >= 10 else 0.5
        if n_u < 15:
            base_high = 0.35
        elif n_u < 100:
            base_high = 0.45
        else:
            base_high = 0.55

        return {
            "n_users": int(n_u),
            "n_items": int(n_i),
            "density_pct": round((hint.get("density") or 0) * 100, 1),
            "total_signals": int(hint.get("total_signals") or 0),
            "alpha_user_cf": alpha,
            "base_threshold": base_high,
            "knn_k": min(16, max(n_u - 1, 1)) if n_u > 1 else 0,
            "shrinkage_lambda": self.cache.get("shrinkage_lambda", 3),
            "scale_class": "kecil" if n_u < 15 else ("menengah" if n_u < 100 else "besar"),
        }

    def get_hybrid_recs(self, user_id: int, top_n: int = 8, 
                        explore_mode: bool = False, 
                        return_metadata: bool = False) -> List[Any]:
        """
        Menghasilkan rekomendasi hybrid dengan mode eksplorasi opsional.
        - explore_mode=False -> Balanced (High Confidence)
        - explore_mode=True -> High Exploration (Diversity focused)
        """
        self.compute_similarity()
        if self.cache["pivot"] is None: return []
        
        is_known_user = user_id in self.cache["pivot"].index if user_id > 0 else False
        
        if is_known_user:
            # 1. Ambil kandidat mentah
            u_raw = self._get_user_recs(user_id, top_n=100)
            i_raw = self._get_item_recs(user_id, top_n=100)
            
            # 2. Global Scaling agar sebanding (v5.1 Fix)
            u_norm, i_norm = self._normalize_scores_global(u_raw, i_raw)
            
            # 3. Identifikasi Hybrid (Ada di kedua model)
            hybrid_ids = set(u_norm.keys()) & set(i_norm.keys())
            
            # 4. Scoring dengan Alpha Weight — AUTO-TUNED v5.4
            # Hyperparameter menyesuaikan skala data aktual (lihat get_active_params):
            #   - Data kecil (<15 user): threshold rendah agar kandidat CF benar-benar muncul,
            #     alpha diturunkan karena User-CF kurang reliabel dengan sedikit tetangga.
            #   - Data besar: kembali ke tuning standar (0.6 / 0.55).
            params = self.get_active_params()
            alpha = params["alpha_user_cf"]
            base_high = params["base_threshold"]
            u_only = [pid for pid in u_norm if pid not in hybrid_ids]
            i_only = [pid for pid in i_norm if pid not in hybrid_ids]
            # Thresholding untuk Elite picks
            high_threshold = base_high + (0.10 if explore_mode else 0.0)
            high_conf = [pid for pid in hybrid_ids if (alpha * u_norm[pid] + (1-alpha) * i_norm[pid]) > high_threshold]
            med_conf = [pid for pid in hybrid_ids if pid not in high_conf]
            
            # Seed DETERMINISTIK (v5.4 fix konsistensi): hanya berubah bila DATA berubah,
            # bukan mengikuti jam — hasil preview & katalog stabil selama data tetap.
            random.seed(f"{user_id}|{self.get_db_signature()}")

            selected = []
            used_ids = set()
            
            # Helper untuk menambah produk dengan aman (v5.1 Empty Check)
            def add_to_selected(pids, limit_count):
                if not pids or limit_count <= 0: return
                added = 0
                for pid in pids:
                    p_int = int(pid)
                    if p_int not in used_ids:
                        selected.append(p_int)
                        used_ids.add(p_int)
                        added += 1
                        if added >= limit_count: break

            # 5. Distribusi hasil (Dynamic Explore Mode v5.3)
            u_only = [pid for pid in u_norm if pid not in hybrid_ids]
            i_only = [pid for pid in i_norm if pid not in hybrid_ids]
            remaining_candidates = u_only + i_only
            
            if explore_mode:
                # Mode Eksplorasi: Diversifikasi acak dari berbagai pool
                if high_conf: add_to_selected(random.sample(high_conf, min(len(high_conf), top_n // 5)), top_n // 5)
                if med_conf: add_to_selected(random.sample(med_conf, min(len(med_conf), top_n // 5)), top_n // 5)
                if remaining_candidates: add_to_selected(random.sample(remaining_candidates, min(len(remaining_candidates), top_n * 3 // 5)), top_n * 3 // 5)
            else:
                # Mode Normal: Prioritas Akurasi (Elite & Hybrid)
                add_to_selected(high_conf, top_n // 2)
                add_to_selected(med_conf, top_n // 4)
                add_to_selected(remaining_candidates, top_n // 4)
                
            # Fallback jika kurang
            if len(selected) < top_n:
                # Prioritas 1: Most Viewed
                most_viewed = self.get_most_viewed_ids(top_n)
                add_to_selected(most_viewed, top_n - len(selected))
                    
                # Prioritas 2: Trending (Sales/Rating)
                if len(selected) < top_n:
                    trending = self.get_popular_ids(top_n)
                    add_to_selected(trending, top_n - len(selected))
            
            # v5.4: TANPA shuffle pada mode normal — urutan prioritas dipertahankan
            # agar hasil konsisten (deterministik) selama data tidak berubah.
            
            selected = selected[:top_n]
            # Track origins for metadata
            origins = {}
            # CF-based: UNLIMITED (Tampilkan semua selama skor > 0)
            # Statistical-based: ELITE 5 ONLY (Hanya Top 5)
            mv_ids = self.get_most_viewed_ids(5)
            bs_ids = self.get_best_sellers(5)
            tr_ids = self.get_popular_ids(5)
            
            for pid in selected:
                p_int = int(pid)
                if p_int in hybrid_ids: origins[p_int] = "BEST MATCH"
                elif p_int in u_norm: origins[p_int] = "FOR YOU"
                elif p_int in i_norm: origins[p_int] = "STYLE MATCH"
                elif p_int in mv_ids: origins[p_int] = "HOT HITS"
                elif p_int in bs_ids: origins[p_int] = "BEST SELLER"
                elif p_int in tr_ids: origins[p_int] = "TRENDING"
                else: origins[p_int] = None # BERSIH
                
            if return_metadata:
                return [{"id": int(pid), "origin": origins.get(int(pid))} for pid in selected]
            return [int(x) for x in selected]
            
        # Fallback untuk guest (v5.1 Cleaned Redundant Code)
        pop_ids = self.get_popular_ids(top_n)
        if return_metadata:
            return [{"id": int(pid), "origin": "TRENDING"} for pid in pop_ids]
        return [int(x) for x in pop_ids]

    # ==========================================================
    # ITEM SIMILARITY — Untuk Related Products di Detail Page
    # ==========================================================

    def get_similar_items(self, product_id: int, top_n: int) -> List[int]:
        """
        Mencari produk paling mirip berdasarkan Cosine Similarity (Item-Based).
        Digunakan untuk 'Related Products' di halaman detail.
        """
        self.compute_similarity()
        item_sim = self.cache["item_sim"]
        
        if item_sim is None or product_id not in item_sim.index:
            return []
            
        sim_scores = item_sim[product_id].sort_values(ascending=False)
        similar_ids = sim_scores.index[1:top_n+1].tolist()
        
        # Hanya ambil yang memiliki skor similarity di atas ambang batas
        valid_ids = []
        for pid in similar_ids:
            if sim_scores[pid] > 0.01:
                valid_ids.append(int(pid))
                
        return valid_ids

    # ==========================================================
    # COLD START HANDLING - Untuk User & Product Baru
    # ==========================================================

    def get_cold_start_recs(self, user_id: int = None, session_id: str = None, 
                            limit: int = 8, context: str = "home") -> List[Dict]:
        """
        Menangani cold start untuk user baru atau guest.
        Menggunakan hybrid approach: Content-Based + Popularity + Random Exploration
        """
        
        # Cek apakah user sudah memiliki interaksi
        has_interactions = False
        if user_id and user_id > 0:
            conn = self.get_db_connection()
            try:
                with conn.cursor() as cursor:
                    cursor.execute("""
                        SELECT COUNT(*) as count FROM (
                            SELECT id FROM ratings WHERE user_id = %s
                            UNION ALL
                            SELECT id FROM orders WHERE user_id = %s
                            UNION ALL
                            SELECT id FROM cart WHERE user_id = %s
                            UNION ALL
                            SELECT id FROM product_views WHERE user_id = %s
                        ) as interactions
                    """, (user_id, user_id, user_id, user_id))
                    result = cursor.fetchone()
                    has_interactions = result['count'] > 0 if result else False
            finally:
                conn.close()
        
        # Jika user sudah punya interaksi, gunakan CF normal
        if has_interactions:
            recs = self.get_hybrid_recs(user_id, top_n=limit, return_metadata=True)
            if recs: return recs
        
        # ==========================================
        # COLD START STRATEGY (User Baru / Guest)
        # ==========================================
        
        cold_start_recs = []
        used_ids = set()
        
        # 1. Get user context dari session (browser, location, referrer)
        user_context = self._get_session_context(session_id)
        
        # 2. Strategi Cold Start:
        # - 30% Trending Now (penjualan/rating)
        # - 30% Most Viewed (paling banyak diklik)
        # - 20% Based on session context (kategori)
        # - 20% New arrivals (freshness)
        
        trending_count = int(limit * 0.3)
        viewed_count = int(limit * 0.3)
        context_count = int(limit * 0.2)
        new_count = limit - (trending_count + viewed_count + context_count)
        
        # A. Most Viewed (ELITE TOP 5)
        most_viewed = self.get_most_viewed_ids(limit=5)
        for pid in most_viewed:
            if pid not in used_ids:
                cold_start_recs.append({
                    "id": int(pid),
                    "origin": "HOT HITS",
                    "reason": "5 Produk Terpopuler Dilihat"
                })
                used_ids.add(pid)

        # B. Trending Now (ELITE TOP 5 - v5.1 Corrected Limit)
        trending = self.get_popular_ids(limit=50) # Ambil pool besar untuk seleksi
        trending_added = 0
        for pid in trending:
            if pid not in used_ids:
                cold_start_recs.append({
                    "id": int(pid),
                    "origin": "TRENDING",
                    "reason": "5 Produk Trending Saat Ini"
                })
                used_ids.add(pid)
                trending_added += 1
                if trending_added >= 5: break
        
        # B. Context-Based (berdasarkan kategori yang dilihat di session)
        if user_context.get('viewed_categories'):
            conn = self.get_db_connection()
            try:
                with conn.cursor() as cursor:
                    format_cats = ','.join(['%s'] * len(user_context['viewed_categories']))
                    cursor.execute(f"""
                        SELECT p.id, p.name, p.category_id
                        FROM products p
                        WHERE p.category_id IN ({format_cats})
                        AND p.id NOT IN ({','.join(['%s'] * len(used_ids)) if used_ids else '0'})
                        ORDER BY p.id DESC
                        LIMIT %s
                    """, tuple(user_context['viewed_categories'] + list(used_ids) + [context_count]))
                    
                    for row in cursor.fetchall():
                        if row['id'] not in used_ids:
                            cold_start_recs.append({
                                "id": int(row['id']),
                                "origin": "Based on your interest",
                                "reason": f"Kategori {row.get('category_id', '')}"
                            })
                            used_ids.add(row['id'])
            finally:
                conn.close()
        
        # C. New Arrivals (Freshness)
        new_products = self.get_new_arrivals(limit=new_count * 2)
        for pid in new_products:
            if pid not in used_ids and len([r for r in cold_start_recs if r['origin'] == "New Arrival"]) < new_count:
                cold_start_recs.append({
                    "id": int(pid),
                    "origin": "New Arrival",
                    "reason": "Produk terbaru"
                })
                used_ids.add(pid)
        
        # D. Best Seller Fallback (Elite Top 5)
        if len(cold_start_recs) < limit:
            best_sellers = self.get_best_sellers(limit=5)
            for pid in best_sellers:
                if pid not in used_ids:
                    cold_start_recs.append({
                        "id": int(pid),
                        "origin": "BEST SELLER",
                        "reason": "Top 5 Penjualan Terbanyak"
                    })
                    used_ids.add(pid)
                if len(cold_start_recs) >= limit: break

        return cold_start_recs[:limit]

    def _get_session_context(self, session_id: str) -> Dict:
        """Mengambil konteks user dari session (tanpa login)"""
        if not session_id:
            return {'viewed_categories': [], 'viewed_products': []}
        
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                # Ambil kategori yang paling sering dilihat di session ini
                cursor.execute("""
                    SELECT p.category_id, COUNT(*) as view_count
                    FROM product_views v
                    JOIN products p ON p.id = v.product_id
                    WHERE v.session_id = %s AND v.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    GROUP BY p.category_id
                    ORDER BY view_count DESC
                    LIMIT 3
                """, (session_id,))
                viewed_categories = [row['category_id'] for row in cursor.fetchall() if row['category_id']]
                
                cursor.execute("""
                    SELECT product_id
                    FROM product_views
                    WHERE session_id = %s AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    ORDER BY created_at DESC
                    LIMIT 10
                """, (session_id,))
                viewed_products = [row['product_id'] for row in cursor.fetchall()]
                
                return {
                    'viewed_categories': viewed_categories,
                    'viewed_products': viewed_products
                }
        except:
            return {'viewed_categories': [], 'viewed_products': []}
        finally:
            conn.close()

    def handle_new_product_cold_start(self, product_id: int) -> None:
        """
        Menangani produk baru agar cepat terdiscover.
        Memberikan boost sementara pada produk baru.
        """
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                # 1. Tambahkan ke tabel new_products_boost (expire dalam 14 hari)
                cursor.execute("""
                    INSERT INTO new_products_boost (product_id, boost_score, expires_at)
                    VALUES (%s, 1.5, DATE_ADD(NOW(), INTERVAL 14 DAY))
                    ON DUPLICATE KEY UPDATE 
                    boost_score = 1.5,
                    expires_at = DATE_ADD(NOW(), INTERVAL 14 DAY)
                """, (product_id,))
                
                # 2. Auto-add ke beberapa kategori trending sebagai "New"
                cursor.execute("""
                    INSERT IGNORE INTO product_views (user_id, product_id, session_id, created_at, source)
                    SELECT NULL, %s, 'system_boost', NOW(), 'system'
                    FROM (SELECT 1 UNION SELECT 2 UNION SELECT 3) as dummy
                    LIMIT 3
                """, (product_id,))
                
                conn.commit()
                logger.info(f"New product {product_id} added to cold start boost")
        except Exception as e:
            logger.error(f"Error handling new product cold start: {e}")
        finally:
            conn.close()

    def get_hybrid_with_cold_start(self, user_id: int, top_n: int = 8, 
                                   session_id: str = None,
                                   return_metadata: bool = False) -> List[Any]:
        """
        VERSI FINAL: Hybrid CF + Cold Start Handling
        """
        self.compute_similarity()
        
        # Cek apakah ini user baru (cold start)
        is_cold_start_user = False
        if user_id and user_id > 0:
            pivot = self.cache.get("pivot")
            if pivot is not None:
                is_cold_start_user = user_id not in pivot.index
            else:
                is_cold_start_user = True
        else:
            is_cold_start_user = True  # Guest user
        
        # Jika cold start user, gunakan cold start strategy
        if is_cold_start_user:
            cold_start_recs = self.get_cold_start_recs(user_id, session_id, top_n, "home")
            if return_metadata:
                return cold_start_recs
            return [rec['id'] for rec in cold_start_recs]
        
        # User existing, lanjutkan dengan CF normal
        recs = self.get_hybrid_recs(user_id, top_n, explore_mode=False, return_metadata=return_metadata)
        
        # Jika CF gagal (misal karena tidak ada irisan produk sama sekali)
        if not recs:
            cold_start_recs = self.get_cold_start_recs(user_id, session_id, top_n, "home")
            if return_metadata:
                return cold_start_recs
            return [rec['id'] for rec in cold_start_recs]
            
        return recs

    def warm_start_exploration(self, user_id: int, limit: int = 8) -> List[int]:
        """
        Untuk user yang baru memiliki sedikit interaksi (warm start).
        Mix antara CF dan cold start strategy.
        """
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                # Hitung jumlah interaksi user
                cursor.execute("""
                    SELECT COUNT(*) as total FROM (
                        SELECT id FROM ratings WHERE user_id = %s
                        UNION ALL
                        SELECT id FROM orders WHERE user_id = %s
                        UNION ALL
                        SELECT id FROM cart WHERE user_id = %s
                    ) as interactions
                """, (user_id, user_id, user_id))
                interaction_count = cursor.fetchone()['total']
        finally:
            conn.close()
        
        # Jika interaksi < 5, masih warm start
        if interaction_count < 5:
            # 60% dari CF, 40% dari cold start
            cf_recs = self.get_hybrid_recs(user_id, top_n=int(limit * 0.6), return_metadata=True)
            cold_recs = self.get_cold_start_recs(user_id, limit=int(limit * 0.4))
            
            # Gabungkan dan deduplikasi
            final_ids = []
            for rec in cf_recs + cold_recs:
                pid = rec['id'] if isinstance(rec, dict) else rec
                if pid not in final_ids:
                    final_ids.append(pid)
            
            return final_ids[:limit]
        
        # Sudah cukup interaksi, gunakan CF penuh
        return self.get_hybrid_recs(user_id, top_n=limit)

    def get_products_with_new_items_boost(self, category_id: int = None, limit: int = 24) -> List[Dict]:
        """
        Menampilkan produk dengan boost untuk produk baru.
        Untuk memastikan produk baru muncul di katalog.
        """
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                if category_id:
                    cursor.execute("""
                        SELECT p.*, 
                               COALESCE(npb.boost_score, 1.0) as boost_score,
                               CASE 
                                   WHEN npb.product_id IS NOT NULL THEN 'New'
                                   ELSE 'Regular'
                               END as status
                        FROM products p
                        LEFT JOIN new_products_boost npb ON npb.product_id = p.id 
                            AND npb.expires_at > NOW()
                        WHERE p.category_id = %s
                        ORDER BY 
                            boost_score DESC,
                            CASE WHEN p.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 0 ELSE 1 END,
                            p.created_at DESC
                        LIMIT %s
                    """, (category_id, limit))
                else:
                    cursor.execute("""
                        SELECT p.*, 
                               COALESCE(npb.boost_score, 1.0) as boost_score,
                               CASE 
                                   WHEN npb.product_id IS NOT NULL THEN 'New'
                                   ELSE 'Regular'
                               END as status
                        FROM products p
                        LEFT JOIN new_products_boost npb ON npb.product_id = p.id 
                            AND npb.expires_at > NOW()
                        ORDER BY 
                            boost_score DESC,
                            CASE WHEN p.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 0 ELSE 1 END,
                            RAND()
                        LIMIT %s
                    """, (limit,))
                
                return list(cursor.fetchall())
        finally:
            conn.close()

    # ==========================================================
    # POPULAR FALLBACK — Cold Start Solution
    # ==========================================================
    def get_popular_ids(self, limit: int = 8) -> List[int]:
        """Mengambil produk terpopuler berdasarkan penjualan dan rating."""
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                # 1. Dari order (paling banyak dibeli) yang sukses
                cursor.execute("""
                    SELECT oi.product_id, SUM(oi.qty) as total_sold
                    FROM order_items oi
                    JOIN orders o ON o.id = oi.order_id
                    JOIN products p ON p.id = oi.product_id
                    WHERE o.status IN ('pending', 'paid', 'shipped', 'delivered')
                    GROUP BY oi.product_id
                    ORDER BY total_sold DESC, oi.product_id ASC
                    LIMIT %s
                """, (limit,))
                results = [r['product_id'] for r in cursor.fetchall()]
                
                # 2. Fallback ke rating tertinggi
                if len(results) < limit:
                    cursor.execute("SELECT product_id FROM ratings GROUP BY product_id ORDER BY AVG(rating) DESC, COUNT(id) DESC, product_id ASC LIMIT %s", (limit,))
                    results.extend([r['product_id'] for r in cursor.fetchall() if r['product_id'] not in results])
                    
                # 3. Fallback deterministik ke produk terbaru (v5.4: tanpa RAND())
                if len(results) < limit:
                    cursor.execute("SELECT id as product_id FROM products ORDER BY created_at DESC, id ASC LIMIT %s", (limit,))
                    results.extend([r['product_id'] for r in cursor.fetchall() if r['product_id'] not in results])
                    
                return results[:limit]
        finally: conn.close()

    def get_most_viewed_ids(self, limit: int = 8) -> List[int]:
        """Mengambil produk yang paling sering dilihat di database."""
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                cursor.execute("""
                    SELECT product_id, COUNT(*) as view_count
                    FROM product_views
                    GROUP BY product_id
                    ORDER BY view_count DESC, product_id ASC
                    LIMIT %s
                """, (limit,))
                return [r['product_id'] for r in cursor.fetchall()]
        except: return []
        finally: conn.close()

    def get_best_sellers(self, limit: int = 10) -> List[int]:
        """Mengambil produk murni berdasarkan jumlah terjual terbanyak (Best Seller)."""
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                cursor.execute("""
                    SELECT oi.product_id, SUM(oi.qty) as total_sold
                    FROM order_items oi
                    JOIN orders o ON o.id = oi.order_id
                    WHERE o.status IN ('pending', 'paid', 'shipped', 'delivered')
                    GROUP BY oi.product_id
                    ORDER BY total_sold DESC, oi.product_id ASC
                    LIMIT %s
                """, (limit,))
                return [r['product_id'] for r in cursor.fetchall()]
        except: return []
        finally: conn.close()

    def get_last_viewed_product(self, user_id: int) -> dict:
        """Mengambil info produk terakhir yang dilihat user."""
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                cursor.execute("""
                    SELECT p.id, p.name 
                    FROM product_views v
                    JOIN products p ON p.id = v.product_id
                    WHERE v.user_id = %s
                    ORDER BY v.created_at DESC LIMIT 1
                """, (user_id,))
                return cursor.fetchone()
        except: return None
        finally: conn.close()

    # ==========================================================
    # SECTIONED RECOMMENDATIONS — Untuk Home & Katalog
    # ==========================================================

    def get_new_arrivals(self, limit: int = 8) -> List[int]:
        """Mengambil produk terbaru."""
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                cursor.execute("SELECT id FROM products ORDER BY created_at DESC LIMIT %s", (limit,))
                return [row['id'] for row in cursor.fetchall()]
        except: return []
        finally: conn.close()

    def get_sectioned_recs(self, user_id: int, limit_per_section: int = 8, session_id: str = None, force_refresh: bool = False):
        """
        Menghasilkan rekomendasi dengan variasi kategori (Diversity Focused).
        Dengan caching agar tidak hit DB terlalu sering.
        """
        is_guest = (user_id is None or user_id == 0)
        cache_key = f"sectioned_recs_{user_id}_{session_id}_{limit_per_section}"
        
        # Cache dimatikan agar perhitungan selalu real-time
        # if not force_refresh and hasattr(self, 'section_cache'):
        #     cached = self.section_cache.get(cache_key)
        #     if cached and (time.time() - cached['timestamp']) < 300:  # 5 menit cache
        #         return cached['data']
        
        logger.info(f"Generating sectioned recommendations for user {user_id}")
        self.compute_similarity()
        if self.cache["pivot"] is None: return []

        try:
            # 0. Ambil data kategori produk
            conn = self.get_db_connection()
            product_categories = {}
            try:
                with conn.cursor() as cursor:
                    cursor.execute("SELECT id, category_id FROM products")
                    for row in cursor.fetchall():
                        product_categories[row['id']] = row['category_id']
            finally: conn.close()
    
            # 1. Ambil kandidat dasar
            u_raw = self._get_user_recs(user_id, top_n=50)
            i_raw = self._get_item_recs(user_id, top_n=50)
            u_norm, i_norm = self._normalize_scores_global(u_raw, i_raw)
            
            shown_ids = set()
            sections = []
            
            # Helper untuk mengisi section dengan variety kategori.
            # Item tetap dalam urutan relevansi (pool sudah di-sort desc),
            # tanpa shuffle — sehingga produk TERBAIK selalu di posisi teratas.
            def fill_section(pool_ids, max_per_cat=2):
                items = []
                cat_counts = {}
                for pid in pool_ids:
                    if pid not in shown_ids:
                        cat = product_categories.get(pid, 0)
                        if cat_counts.get(cat, 0) < max_per_cat:
                            items.append(pid)
                            cat_counts[cat] = cat_counts.get(cat, 0) + 1
                            if len(items) >= limit_per_section: break
                
                # Fill remaining if needed
                if len(items) < limit_per_section:
                    for pid in pool_ids:
                        if pid not in shown_ids and pid not in items:
                            items.append(pid)
                            if len(items) >= limit_per_section: break
                
                shown_ids.update(items)
                return items

            # --- Split CF produk ke 3 section: Hybrid, User-Based, Item-Based ---
            # Agar badge lebih beragam, bagi produk secara adil ke tiap section.
            
            # Hybrid HANYA jika kedua model memberikan skor > 0
            hybrid_ids = set([pid for pid in u_norm if u_norm[pid] > 0]) & set([pid for pid in i_norm if i_norm[pid] > 0])
            
            # Sisanya masuk ke spesifik model (v5.2 Set for O(1) lookup)
            u_only_ids = {pid for pid in u_norm if pid not in hybrid_ids and u_norm[pid] > 0}
            i_only_ids = {pid for pid in i_norm if pid not in hybrid_ids and i_norm[pid] > 0}
            
            # Jika ada yang skor keduanya 0 karena normalisasi, fallback ke asal utamanya
            for pid in set(u_norm.keys()) | set(i_norm.keys()):
                if pid not in hybrid_ids and pid not in u_only_ids and pid not in i_only_ids:
                    if pid in u_norm and pid not in i_norm: u_only_ids.add(pid)
                    elif pid in i_norm and pid not in u_norm: i_only_ids.add(pid)
                    elif u_norm.get(pid, 0) > i_norm.get(pid, 0): u_only_ids.add(pid)
                    elif i_norm.get(pid, 0) > u_norm.get(pid, 0): i_only_ids.add(pid)
                    else: hybrid_ids.add(pid)

            # Elite IDs for reference (CF is now Uncapped)
            mv_ids = self.get_most_viewed_ids(5)
            bs_ids = self.get_best_sellers(5)
            tr_ids = self.get_popular_ids(5)

            def get_item_origin(pid):
                p_int = int(pid)
                if p_int in hybrid_ids: return "BEST MATCH"
                if p_int in u_only_ids: return "FOR YOU"
                if p_int in i_only_ids: return "STYLE MATCH"
                if p_int in mv_ids: return "HOT HITS"
                if p_int in bs_ids: return "BEST SELLER"
                if p_int in tr_ids: return "TRENDING"
                return None

            # 1. Personalized (Hybrid) — produk yang muncul di KEDUA model CF
            hybrid_sorted = sorted(hybrid_ids, key=lambda x: u_norm[x] + i_norm[x], reverse=True)
            # Ambil separuh untuk Hybrid, sisanya distribusikan ke User/Item section
            hybrid_for_section = hybrid_sorted[:max(len(hybrid_sorted) // 2, 1)]
            hybrid_remainder = hybrid_sorted[len(hybrid_for_section):]
            
            hybrid_items = fill_section(hybrid_for_section)
            if hybrid_items:
                sections.append({
                    "title": "Rekomendasi untuk Anda",
                    "origin": "Hybrid",
                    "items": [{"id": int(pid), "origin": get_item_origin(pid)} for pid in hybrid_items]
                })

            # 2. Item-Based — produk dari Item CF (kontekstual: "karena Anda menyukai")
            last_viewed = self.get_last_viewed_product(user_id)
            i_pool = list(i_only_ids) + [pid for pid in hybrid_remainder if pid not in shown_ids]
            i_pool_sorted = sorted(i_pool, key=lambda x: i_norm.get(x, 0), reverse=True)
            item_items = fill_section(i_pool_sorted)
            if item_items:
                title = f"Karena Anda menyukai {last_viewed['name']}" if last_viewed else "Produk yang mungkin Anda sukai"
                sections.append({
                    "title": title,
                    "origin": "Item-Based",
                    "items": [{"id": int(pid), "origin": get_item_origin(pid)} for pid in item_items]
                })

            # 3. User-Based — produk dari User CF (sosial)
            u_pool = list(u_only_ids) + [pid for pid in hybrid_remainder if pid not in shown_ids]
            u_pool_sorted = sorted(u_pool, key=lambda x: u_norm.get(x, 0), reverse=True)
            user_items = fill_section(u_pool_sorted)
            if user_items:
                sections.append({
                    "title": "Pengguna lain juga menyukai ini",
                    "origin": "User-Based",
                    "items": [{"id": int(pid), "origin": get_item_origin(pid)} for pid in user_items]
                })

            # 4. Trending
            pop_ids = self.get_popular_ids(limit=50)
            trending_items = fill_section(pop_ids)
            if trending_items:
                sections.append({
                    "title": "Sedang tren",
                    "origin": "Trending",
                    "items": [{"id": int(pid), "origin": "Trending Now"} for pid in trending_items]
                })

            # 5. New Arrivals
            new_items = self.get_new_arrivals(limit=limit_per_section)
            new_filtered = [pid for pid in new_items if pid not in shown_ids]
            if new_filtered:
                sections.append({
                    "title": "Baru tiba untukmu",
                    "origin": "Fresh",
                    "items": [{"id": int(pid), "origin": "New Arrival"} for pid in new_filtered]
                })

            if not sections:
                trending = self.get_popular_ids(limit_per_section * 2)
                new_arrivals = self.get_new_arrivals(limit_per_section)
                all_products = list(dict.fromkeys(trending + new_arrivals))[:limit_per_section * 3]
                
                sections = [{
                    "title": "Produk Rekomendasi",
                    "origin": "Fallback",
                    "items": [{"id": int(pid), "origin": "Popular"} for pid in all_products[:limit_per_section]]
                }]

            # Cache dinonaktifkan
            # if not hasattr(self, 'section_cache'):
            #     self.section_cache = {}
            # self.section_cache[cache_key] = {
            #     'data': sections,
            #     'timestamp': time.time()
            # }

            return sections
            
        except Exception as e:
            logger.error(f"Error in get_sectioned_recs: {e}")
            return [{
                "title": "Produk Populer",
                "origin": "Fallback",
                "items": [{"id": int(pid), "origin": "Popular"} for pid in self.get_popular_ids(limit_per_section)]
            }]

    # ==========================================================
    # MODEL EVALUATION — MAE & RMSE
    # ==========================================================

    def evaluate_model(self, test_size: float = 0.2, k_val: int = None) -> Dict[str, Any]:
        """
        Mengevaluasi performa prediksi model menggunakan MAE dan RMSE.
        k_val: Optional, untuk testing nilai K tertentu (Optimization).
        """
        df = self.fetch_enhanced_ratings()
        if df.empty or len(df) < 10:
            return {"mae": 0.0, "rmse": 0.0, "samples": 0}

        try:
            # 1. Time-based Split
            df = df.sort_values('created_at')
            split_idx = int(len(df) * (1 - test_size))
            train_df = df.iloc[:split_idx]
            test_df = df.iloc[split_idx:]
            
            # 2. Build model pada Training Set
            pivot_train = train_df.pivot_table(index="user_id", columns="product_id", values="rating")
            
            # Mean-Centering
            user_mean = pivot_train.mean(axis=1)
            pivot_centered_train = pivot_train.sub(user_mean, axis=0).fillna(0)
            
            # Buat KNN model (Gunakan k_val jika disediakan)
            current_k = k_val if k_val else min(16, len(pivot_train))
            if len(pivot_train) < 2: return {"mae": 0.0, "rmse": 0.0, "samples": 0}
            
            knn_train = NearestNeighbors(n_neighbors=min(current_k, len(pivot_train)), metric='cosine', algorithm='brute')
            knn_train.fit(csr_matrix(pivot_centered_train.values))
            
            actuals = []
            predictions = []
            
            for _, row in test_df.iterrows():
                uid = row['user_id']
                pid = row['product_id']
                actual = row['rating']
                pred = self._predict_rating_for_eval(uid, pid, pivot_train, pivot_centered_train, knn_train)
                if pred is not None:
                    actuals.append(actual)
                    predictions.append(pred)
            
            if not actuals:
                return {"mae": 0.0, "rmse": 0.0, "samples": 0}
                
            mae = mean_absolute_error(actuals, predictions)
            rmse = np.sqrt(mean_squared_error(actuals, predictions))
            
            return {
                "mae": round(float(mae), 4),
                "rmse": round(float(rmse), 4),
                "samples": len(actuals),
                "k_used": current_k,
                "eligible": len(actuals) >= 10
            }
        except Exception as e:
            logger.error(f"Error in evaluate_model: {e}")
            return {"mae": 0.0, "rmse": 0.0, "samples": 0, "eligible": False}

    def evaluate_ranking(self, k: int = 5) -> Dict[str, Any]:
        """
        Evaluasi Ranking Top-N (v5.5) — praktik standar untuk sistem rekomendasi
        dengan dominan sinyal implisit (ref: Microsoft Recommenders / SAR):
          - Split leave-last-out: interaksi TERAKHIR tiap user jadi test set.
          - Model dilatih ulang pada sisanya, lalu diperingkat untuk kandidat unseen.
          - Metrik: Precision@K, Recall@K, NDCG@K (relevansi biner, remove seen).
        Catatan: pada leave-one-out Recall@K ≡ Hit-Rate@K.
        """
        empty = {
            "precision_at_k": None, "recall_at_k": None, "ndcg_at_k": None,
            "k": k, "eval_users": 0, "eligible": False,
            "reason": "Data interaksi belum cukup untuk evaluasi ranking (butuh ≥5 user dengan ≥2 interaksi)"
        }
        
        try:
            df = self.fetch_enhanced_ratings()
            if df.empty or 'created_at' not in df.columns: return empty
            
            # --- Leave-last-out split ---
            df_sorted = df.sort_values(['user_id', 'created_at'])
            test_rows = df_sorted.groupby('user_id').tail(1)
            train_df = df_sorted.drop(test_rows.index)
            
            # Eligibility: ≥5 user punya ≥2 interaksi
            counts = df_sorted.groupby('user_id').size()
            eval_user_ids = counts[counts >= 2].index.tolist()
            if len(eval_user_ids) < 5 or len(train_df) < 5: return empty
            
            # --- Latih ulang model offline pada train_df ---
            pivot_train = train_df.pivot_table(index="user_id", columns="product_id", values="rating")
            pivot_train = pivot_train.fillna(0)
            
            user_mean = pivot_train.replace(0, np.nan).mean(axis=1).fillna(0)
            pivot_centered = pivot_train.sub(user_mean, axis=0)
            
            binary = (pivot_train > 0).astype(int)
            co = np.dot(binary.T, binary)
            
            sim_items = cosine_similarity(pivot_centered.T)
            sim_items = np.nan_to_num(sim_items, nan=0.0) * (co / (co + self.cache.get("shrinkage_lambda", 3)))
            
            knn_u = NearestNeighbors(n_neighbors=min(16, max(len(pivot_train) - 1, 1)), metric='cosine', algorithm='brute')
            knn_u.fit(csr_matrix(pivot_centered.values))
            
            test_map = {r['user_id']: r['product_id'] for _, r in test_rows.iterrows()}
            hint = self.cache.get("scale_hint") or {}
            alpha = 0.6 if len(pivot_train) >= 10 else 0.5
            k_neigh_item = max(min(16, int(hint.get("n_items", 16)) - 1), 1)
            
            pids_all = list(pivot_train.columns)
            pid_pos = {p: i for i, p in enumerate(pids_all)}
            users_list = list(pivot_train.index)
            u_pos = {u: i for i, u in enumerate(users_list)}
            
            precisions, recalls, ndcgs = [], [], []
            for uid in eval_user_ids:
                true_pid = test_map.get(uid)
                if uid not in u_pos or true_pid not in pid_pos: continue
                
                ui = u_pos[uid]
                
                # Skor User-CF (deviasi terboboti, dinormalisasi denominasi)
                dists, nbr_idx = knn_u.kneighbors(pivot_centered.values[[ui]])
                sims = 1 - dists[0]
                num = {}; den = {}
                for j, nidx in enumerate(nbr_idx[0]):
                    s = float(sims[j])
                    if s <= 0.05 or users_list[nidx] == uid: continue
                    row = pivot_train.iloc[nidx]
                    for p, r_v in row.items():
                        if r_v > 0 and pivot_train.iloc[ui][p] == 0:
                            dev = r_v - float(user_mean.iloc[nidx])
                            num[p] = num.get(p, 0.0) + s * dev
                            den[p] = den.get(p, 0.0) + abs(s)
                mu_u = float(user_mean.iloc[ui])
                u_scores = {p: (mu_u + (num[p] / den[p]) - 3.0) / 2.0 for p in num if den[p] > 1e-9}
                
                # Skor Item-CF (weighted average + denominator)
                urow = pivot_train.iloc[ui]
                interacted = [p for p in pids_all if urow[p] > 0]
                inum = {}; iden = {}
                for p in interacted:
                    pi = pid_pos[p]
                    order = np.argsort(sim_items[:, pi])[::-1]
                    taken = 0
                    for qi in order:
                        qp = pids_all[qi]
                        if qp == p or taken >= k_neigh_item: break
                        s = float(sim_items[qi, pi])
                        if s <= 0 or urow[qp] > 0: continue
                        inum[qp] = inum.get(qp, 0.0) + s * float(urow[p])
                        iden[qp] = iden.get(qp, 0.0) + abs(s)
                        taken += 1
                i_scores = {p: ((inum[p] / iden[p]) - 3.0) / 2.0 for p in inum if iden[p] > 1e-9}
                
                # Blend alpha (normalisasi min-max per model, sama seperti runtime)
                def mm(d):
                    if not d: return {}
                    vs = list(d.values()); mn, mx = min(vs), max(vs)
                    return {kk: 0.5 for kk in d} if mx == mn else {kk: (vv - mn) / (mx - mn) for kk, vv in d.items()}
                un, inn = mm(u_scores), mm(i_scores)
                hybrid = {}
                for p in set(un) | set(inn):
                    hybrid[p] = alpha * un.get(p, 0.0) + (1 - alpha) * inn.get(p, 0.0)
                
                top_k = sorted(hybrid.items(), key=lambda x: x[1], reverse=True)[:k]
                ranked = [p for p, _ in top_k]
                
                hits = 1 if true_pid in ranked else 0
                precisions.append(hits / k)
                recalls.append(hits)  # 1 relevan item → recall = hit-rate
                if hits:
                    rank_pos = ranked.index(true_pid) + 1
                    ndcgs.append(1.0 / np.log2(rank_pos + 1))
                else:
                    ndcgs.append(0.0)
            
            n = len(precisions)
            if n == 0: return empty
            return {
                "precision_at_k": round(float(np.mean(precisions)), 4),
                "recall_at_k": round(float(np.mean(recalls)), 4),
                "ndcg_at_k": round(float(np.mean(ndcgs)), 4),
                "k": k,
                "eval_users": n,
                "eligible": True
            }
        except Exception as e:
            logger.error(f"Error in evaluate_ranking: {e}")
            return empty

    # ==========================================================
    # THESIS EXPERIMENTS — K-Optimization & Cross-Validation
    # ==========================================================

    def find_optimal_k(self, k_range: range = range(1, 21)) -> List[Dict]:
        """
        Mencari nilai K optimal dengan melakukan iterasi MAE/RMSE.
        Digunakan untuk visualisasi grafik K-Optimization di skripsi.
        """
        results = []
        for k in k_range:
            eval_res = self.evaluate_model(test_size=0.2, k_val=k)
            if eval_res["samples"] > 0:
                results.append({
                    "k": k,
                    "mae": eval_res["mae"],
                    "rmse": eval_res["rmse"],
                    "samples": eval_res["samples"]
                })
        return results

    def cross_validate_model(self, n_folds: int = 5) -> Dict[str, Any]:
        """
        Evaluasi performa menggunakan K-Fold Cross Validation.
        Lebih robust dibandingkan single split.
        """
        from sklearn.model_selection import KFold
        df = self.fetch_enhanced_ratings()
        if len(df) < 20: return {"status": "error", "message": "Data tidak cukup untuk K-Fold"}

        kf = KFold(n_splits=n_folds, shuffle=True, random_state=42)
        fold_results = []
        
        for train_idx, test_idx in kf.split(df):
            train_df = df.iloc[train_idx]
            test_df = df.iloc[test_idx]
            
            # Re-implementasi evaluasi sederhana untuk fold ini
            try:
                pivot_train = train_df.pivot_table(index="user_id", columns="product_id", values="rating")
                user_mean = pivot_train.mean(axis=1)
                pivot_centered_train = pivot_train.sub(user_mean, axis=0).fillna(0)
                
                knn_train = NearestNeighbors(n_neighbors=min(16, len(pivot_train)), metric='cosine', algorithm='brute')
                knn_train.fit(csr_matrix(pivot_centered_train.values))
                
                actuals, preds = [], []
                for _, row in test_df.iterrows():
                    p = self._predict_rating_for_eval(row['user_id'], row['product_id'], pivot_train, pivot_centered_train, knn_train)
                    if p is not None:
                        actuals.append(row['rating'])
                        preds.append(p)
                
                if actuals:
                    fold_results.append({
                        "mae": mean_absolute_error(actuals, preds),
                        "rmse": np.sqrt(mean_squared_error(actuals, preds))
                    })
            except: continue

        if not fold_results: return {"status": "error", "message": "Gagal melakukan Cross-Validation"}

        return {
            "mae_mean": round(np.mean([r['mae'] for r in fold_results]), 4),
            "rmse_mean": round(np.mean([r['rmse'] for r in fold_results]), 4),
            "mae_std": round(np.std([r['mae'] for r in fold_results]), 4),
            "rmse_std": round(np.std([r['rmse'] for r in fold_results]), 4),
            "folds": len(fold_results)
        }

    def handle_sparsity_with_popularity(self, pivot_matrix: pd.DataFrame) -> pd.DataFrame:
        """
        Menangani sparsity data dengan mengisi rating kosong (0) menggunakan 
        rata-rata rating produk tersebut (Popularity-Based filling).
        Metodologi ini digunakan pada jurnal MIND 2024.
        """
        # Hitung rata-rata rating untuk setiap produk (hanya dari user yang sudah rating)
        # Gunakan raw pivot agar rating asli terjaga
        product_popularity = pivot_matrix.replace(0, np.nan).mean(axis=0).fillna(pivot_matrix.values.mean())
        
        # Isi nilai 0 dengan nilai rata-rata produk
        filled_matrix = pivot_matrix.copy()
        for col in filled_matrix.columns:
            filled_matrix.loc[filled_matrix[col] == 0, col] = product_popularity[col]
            
        return filled_matrix

    def _predict_rating_for_eval(self, user_id, product_id, pivot, pivot_centered, knn_model):
        """
        Helper untuk menghitung prediksi rating R(u,i) menggunakan KNN.
        Formula: R(u,i) = Σ (sim(u,v) * R(v,i)) / Σ sim(u,v)
        """
        if user_id not in pivot_centered.index:
            return None
            
        user_vector = pivot_centered.loc[[user_id]].values
        distances, indices = knn_model.kneighbors(user_vector)
        
        total_sim = 0
        total_weighted_rating = 0
        
        for i, neighbor_idx in enumerate(indices[0]):
            sim_score = 1 - distances[0][i]
            if sim_score <= 0.05: continue
            
            other_user = pivot_centered.index[neighbor_idx]
            if other_user == user_id: continue
            
            # Ambil rating dari tetangga untuk produk target
            if product_id in pivot.columns:
                rating = pivot.loc[other_user, product_id]
                if rating > 0:
                    total_sim += sim_score
                    total_weighted_rating += sim_score * rating
                    
        if total_sim > 0:
            return total_weighted_rating / total_sim
        return None

    # ==========================================================
    # MODEL STATISTICS — Untuk Admin Dashboard
    # ==========================================================

    def get_model_stats(self) -> Dict[str, Any]:
        """Menghasilkan statistik performa model untuk dashboard admin."""
        self.compute_similarity()
        df = self.cache["ratings"]
        pivot = self.cache["pivot"]
        if pivot is None or df is None: return {"status": "empty"}
        
        density = (np.count_nonzero(pivot) / pivot.size) * 100
        
        # Sim Strength (Top 5 avg)
        avg_sim = 0
        item_sim_df = self.cache.get("item_sim")
        if item_sim_df is not None and len(item_sim_df) > 1:
            try:
                # Ambil matrix similarity, abaikan diagonal (item itu sendiri)
                sim_matrix = item_sim_df.values
                # Untuk setiap item, ambil kemiripan tertinggi dengan item lain (top 1 similarity)
                # Kita ambil rata-rata dari kemiripan terkuat di seluruh matrix
                # Masking diagonal agar tidak menghitung kemiripan item dengan dirinya sendiri (1.0)
                mask = ~np.eye(sim_matrix.shape[0], dtype=bool)
                top_similarities = sim_matrix[mask].reshape(sim_matrix.shape[0], -1).max(axis=1)
                avg_sim = np.mean(top_similarities)
            except Exception as e:
                logger.error(f"Error calculating avg_sim: {e}")
                avg_sim = 0
        
        # Coverage — Berapa % user yang bisa mendapat rekomendasi CF
        users_with_recs = 0
        pivot = self.cache.get("pivot")
        if pivot is not None:
            for uid in pivot.index:
                if len(df[df['user_id'] == uid]) >= 3: users_with_recs += 1
            coverage = (users_with_recs / len(pivot.index)) * 100 if len(pivot.index) > 0 else 0
        else:
            coverage = 0
        
        # Source Distribution — pakai hitungan MENTAH (sebelum agregasi) agar
        # like/cart/view yang tumpang tindih dengan rating/purchase tetap terlihat.
        source_dist = self.cache.get("raw_source_dist") or df["source"].value_counts().to_dict()
        
        # Confidence Formula
        density_score = min(density, 50)
        sim_score = min(avg_sim * 100, 60)
        coverage_score = coverage
        
        total_users = len(pivot.index)
        if total_users < 50:
            confidence = (0.2 * density_score) + (0.3 * sim_score) + (0.5 * coverage_score)
        elif total_users < 200:
            confidence = (0.25 * density_score) + (0.4 * sim_score) + (0.35 * coverage_score)
        else:
            confidence = (0.3 * density_score) + (0.5 * sim_score) + (0.2 * coverage_score)
        
        # --- Evaluation Metrics (v5.5) ---
        # Ranking (P@K/R@K/NDCG@K) = metrik utama utk top-N implisit;
        # MAE/RMSE hanya dilaporkan bila sampel uji layak (≥10).
        ranking_metrics = self.evaluate_ranking(k=5)
        eval_metrics = self.evaluate_model()
        mae = eval_metrics.get("mae", 0)
        rmse = eval_metrics.get("rmse", 0)
        
        # Adjust confidence based on error (Penalti jika RMSE tinggi)
        # RMSE ideal adalah < 1.0 pada skala 1-5
        if eval_metrics.get("eligible") and rmse > 1.2: confidence *= 0.9
        if eval_metrics.get("eligible") and rmse > 1.5: confidence *= 0.8
        
        # Top Similarity Pairs (Item-Item)
        top_pairs = []
        item_sim_df = self.cache.get("item_sim")
        co_matrix_df = self.cache.get("co_matrix")
        
        if item_sim_df is not None and len(item_sim_df) > 1:
            conn = self.get_db_connection()
            try:
                with conn.cursor() as cursor:
                    cursor.execute("SELECT id, name FROM products")
                    p_names = {r['id']: r['name'] for r in cursor.fetchall()}
            finally: conn.close()
            
            sim_matrix = item_sim_df.values
            pids = item_sim_df.columns
            upper_indices = np.triu_indices(len(pids), k=1)
            scores = sim_matrix[upper_indices]
            
            # Sort pasangan berdasarkan skor tertinggi
            top_idx = np.argsort(scores)[::-1]
            
            seen_products = set() 
            
            for idx in top_idx:
                if len(top_pairs) >= 10: break 
                
                i, j = upper_indices[0][idx], upper_indices[1][idx]
                score = scores[idx]
                
                # Filter: Skor harus valid (>0.05) dan bukan anomali 100%
                if 0.05 < score < 0.999:
                    pid1, pid2 = pids[i], pids[j]
                    
                    if pid1 not in seen_products and pid2 not in seen_products:
                        seen_products.add(pid1)
                        seen_products.add(pid2)
                        
                        # Ambil co-occurrence count dari matrix
                        co_count = int(co_matrix_df.loc[pid1, pid2]) if co_matrix_df is not None else 0
                        lam = self.cache.get("shrinkage_lambda", 3)
                        shrink_factor = round(co_count / (co_count + lam), 2)
                        
                        top_pairs.append({
                            "p1": p_names.get(pid1, f"ID {pid1}"),
                            "p2": p_names.get(pid2, f"ID {pid2}"),
                            "score": round(float(score), 4),
                            "co_occurrence": co_count,
                            "shrink": shrink_factor
                        })
        
        top_pairs.sort(key=lambda x: x['score'], reverse=True)

        return {
            "density_score": round(density, 2),
            "sim_strength": round(avg_sim * 100, 2),
            "coverage_score": round(coverage, 2),
            "total_users": len(pivot.index),
            "total_items": len(pivot.columns),
            "total_signals": len(df),
            "source_distribution": source_dist,
            "top_similarity_pairs": top_pairs,
            "ranking_metrics": ranking_metrics,
            "mae": mae if eval_metrics.get("eligible") else None,
            "rmse": rmse if eval_metrics.get("eligible") else None,
            "eval_samples": eval_metrics.get("samples", 0),
            "eval_eligible": bool(eval_metrics.get("eligible")),
            "final_confidence": round(min(confidence, 99.9), 1)
        }
