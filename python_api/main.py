"""
JI-DOOR PURE COLLABORATIVE FILTERING API v5.3
Main API Entry Point (FastAPI)

Endpoint rekomendasi real-time berbasis Pure CF (User-Based + Item-Based).
"""

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from recommendation_engine import RecommendationEngine
from variant_recommender import VariantRecommender
from pydantic import BaseModel
import time
from typing import Optional
from collections import Counter

app = FastAPI(title="JiDoor Pure CF Engine v5.5")

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- Pydantic Models ---

class ProductView(BaseModel):
    user_id: int
    product_id: int
    session_id: Optional[str] = None

# --- Initialize ---

DB_CONFIG = {
    "host": "127.0.0.1", "port": 8889, "user": "root", "password": "root", "database": "ecommerce_db",
    "charset": "utf8mb4", "cursorclass": __import__('pymysql').cursors.DictCursor
}

engine = RecommendationEngine(DB_CONFIG)
variant_engine = VariantRecommender(DB_CONFIG)

_api_log = {"total_requests": 0, "endpoints": {}, "started_at": time.strftime("%Y-%m-%d %H:%M:%S")}

def log_request(endpoint: str):
    _api_log["total_requests"] += 1
    if endpoint not in _api_log["endpoints"]:
        _api_log["endpoints"][endpoint] = {"count": 0, "last_called": None}
    _api_log["endpoints"][endpoint]["count"] += 1
    _api_log["endpoints"][endpoint]["last_called"] = time.strftime("%Y-%m-%d %H:%M:%S")

@app.get("/")
def home():
    return {"status": "online", "version": "5.4", "engine": "Pure Collaborative Filtering + Variant Layer"}

# ==========================================================
# RECOMMENDATION ENDPOINTS
# ==========================================================

@app.get("/recommend/{user_id}", tags=["Recommendation"])
def get_recommendations(user_id: int, top_n: int = 8, metadata: bool = False,
                        with_variants: bool = False, session_id: Optional[str] = None):
    """
    Endpoint utama: Pure CF recommendations (User-Based + Item-Based) dengan Cold Start Handling.
    with_variants=true → setiap rekomendasi dilengkapi varian (warna + ukuran) terbaik untuk user.
    """
    log_request("/recommend/{user_id}")

    is_cold_start = engine.cache.get("pivot") is None or user_id not in engine.cache.get("pivot").index if user_id > 0 else True

    if metadata:
        recs = engine.get_hybrid_with_cold_start(user_id, top_n, session_id=session_id, return_metadata=True)
        if with_variants:
            recs = variant_engine.enrich(recs, user_id)
        return {
            "user_id": user_id,
            "recommendations": recs,
            "method": "pure_cf_v5_with_cold_start",
            "is_cold_start": is_cold_start
        }

    product_ids = engine.get_hybrid_with_cold_start(user_id, top_n, session_id=session_id)
    return {
        "user_id": user_id,
        "recommended_product_ids": product_ids,
        "method": "pure_cf_v5_with_cold_start",
        "is_cold_start": is_cold_start
    }

@app.get("/recommend/sections/{user_id}", tags=["Recommendation"])
def get_sectioned_recommendations(user_id: int, limit_per_section: int = 4, with_variants: bool = False):
    """
    Endpoint untuk layout Home Page: Rekomendasi terbagi dalam baris (sections).
    Ditingkatkan dengan kategori variety dan fresh products.
    with_variants=true → tiap item dilengkapi varian (warna + ukuran) terbaik.
    """
    log_request("/recommend/sections/{user_id}")
    sections = engine.get_sectioned_recs(user_id, limit_per_section)
    if with_variants:
        for sec in sections:
            sec["items"] = variant_engine.enrich(sec.get("items", []), user_id)
    return {
        "user_id": user_id,
        "sections": sections,
        "method": "sectioned_hybrid_v5_varied"
    }

@app.get("/recommend/vary/{user_id}", tags=["Recommendation"])
def get_varied_recommendations(user_id: int, variation: str = 'balanced', limit: int = 12):
    """
    Endpoint untuk A/B testing variasi rekomendasi:
    - variation='balanced': Normal hybrid sectioned
    - variation='explore': High exploration mode
    - variation='fresh': Only new/rare products
    """
    log_request(f"/recommend/vary/{user_id}?variation={variation}")
    
    if variation == 'explore':
        recs = engine.get_hybrid_recs(user_id, top_n=limit, explore_mode=True, return_metadata=True)
        return {"user_id": user_id, "recommendations": recs, "variation": variation}

    else:
        sections = engine.get_sectioned_recs(user_id, limit_per_section=4)
        return {"user_id": user_id, "sections": sections, "variation": variation}

@app.get("/recommend/simulate/{user_id}", tags=["Admin"])
def simulate_recommendations(user_id: int, top_n: int = 4):
    """Simulasi rekomendasi lengkap dengan metadata. Untuk dashboard admin."""
    log_request("/recommend/simulate/{user_id}")
    recs = engine.get_hybrid_recs(user_id, top_n, return_metadata=True)
    
    conn = engine.get_db_connection()
    try:
        with conn.cursor() as cursor:
            for r in recs:
                cursor.execute("SELECT name, image, price FROM products WHERE id = %s", (r["id"],))
                p = cursor.fetchone()
                if p:
                    r["name"] = p["name"]
                    r["image"] = p["image"]
                    r["price"] = p["price"]
    finally: conn.close()
    
    return {"user_id": user_id, "recommendations": recs}

@app.get("/recommend/item/{product_id}", tags=["Recommendation"])
def get_item_recommendations(product_id: int, top_n: int = 4):
    """Produk serupa (Item-Based CF). Untuk halaman detail produk."""
    log_request(f"/recommend/item/{product_id}")
    similar_ids = engine.get_similar_items(product_id, top_n)
    return {
        "product_id": product_id,
        "similar_product_ids": similar_ids,
        "method": "item_cosine_similarity"
    }

# ==========================================================
# TRACKING — Hanya product view (implicit signal untuk CF)
# ==========================================================

@app.get("/recommend/variant/{user_id}/{product_id}", tags=["Recommendation"])
def get_variant_recommendation(user_id: int, product_id: int):
    """
    Varian (warna + ukuran) terbaik untuk satu produk bagi user tertentu.
    Dipakai halaman detail produk untuk menandai opsi yang cocok untuk user.
    """
    log_request("/recommend/variant/{user_id}/{product_id}")
    profile = variant_engine.get_user_profile(user_id) if user_id > 0 else {"colors": Counter(), "sizes": Counter()}
    popular = variant_engine.get_global_popularity()
    try:
        variant = variant_engine.pick_for_product(product_id, profile, popular)
    except Exception as e:
        logger.warning(f"Gagal pilih varian utk produk {product_id}: {e}")
        variant = None
    return {"product_id": product_id, "user_id": user_id, "variant": variant}


@app.post("/track/view", tags=["Tracking"])
def track_product_view(view: ProductView):
    """Mencatat setiap kali user melihat detail produk (Implicit Signal untuk CF)."""
    conn = engine.get_db_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS product_views (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    product_id INT,
                    session_id VARCHAR(100),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user (user_id),
                    INDEX idx_product (product_id)
                )
            """)
            sql = "INSERT INTO product_views (user_id, product_id, session_id) VALUES (%s, %s, %s)"
            cursor.execute(sql, (view.user_id, view.product_id, view.session_id))
            conn.commit()
    finally: conn.close()
    return {"status": "success"}

# ==========================================================
# ADMIN ENDPOINTS
# ==========================================================

@app.get("/admin/stats", tags=["Admin"])
@app.get("/admin/stats/enhanced")
def admin_stats():
    """Statistik model CF untuk admin dashboard."""
    log_request("/admin/stats")
    stats = engine.get_model_stats()
    
    # Rating Distribution
    df = engine.cache["ratings"]
    if df is not None:
        dist = df['rating'].round().value_counts().sort_index().to_dict()
        stats["rating_distribution"] = {str(int(k)): int(v) for k, v in dist.items() if 1 <= k <= 5}

    conn = engine.get_db_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute("SELECT COUNT(*) as c FROM users")
            true_total_users = cursor.fetchone()['c']
            cursor.execute("SELECT COUNT(*) as c FROM products")
            true_total_products = cursor.fetchone()['c']
            
            stats["active_users"] = stats.get("total_users", 0)
            stats["active_products"] = stats.get("total_items", 0)
            stats["total_users"] = true_total_users
            stats["total_items"] = true_total_products
    finally: conn.close()
    stats["api_log"] = _api_log
    stats["active_params"] = engine.get_active_params()
    return {"status": "success", "data": stats}

@app.get("/admin/evaluate", tags=["Admin"])
def evaluate_model(test_size: float = 0.2):
    """
    Evaluasi model CF menggunakan MAE dan RMSE dengan Time-based splitting.
    Digunakan untuk analisis kuantitatif pada Bab 4 Skripsi.
    """
    log_request("/admin/evaluate")
    metrics = engine.evaluate_model(test_size)
    return {"status": "success", "metrics": metrics}

@app.get("/admin/optimize-k", tags=["Admin"])
def optimize_k(start: int = 1, end: int = 20):
    """
    Eksperimen untuk mencari nilai K optimal (1-20).
    Menghasilkan data untuk grafik perbandingan MAE/RMSE di skripsi.
    """
    log_request(f"/admin/optimize-k?range={start}-{end}")
    results = engine.find_optimal_k(range(start, end + 1))
    return {"status": "success", "results": results}

@app.get("/admin/cross-validate", tags=["Admin"])
def cross_validate(folds: int = 5):
    """
    Evaluasi performa menggunakan K-Fold Cross Validation.
    Untuk membuktikan stabilitas model secara akademik.
    """
    log_request(f"/admin/cross-validate?folds={folds}")
    metrics = engine.cross_validate_model(folds)
    return {"status": "success", "metrics": metrics}

@app.get("/admin/rec-preview/{user_id}", tags=["Admin"])
def admin_rec_preview(user_id: int, top_n: int = 8):
    """
    Preview output rekomendasi personal v5.4 untuk dashboard admin.
    Mengembalikan barang + origin + saran varian (warna & ukuran) per user —
    persis seperti yang diterima storefront.
    """
    log_request(f"/admin/rec-preview/{user_id}")
    recs = engine.get_hybrid_with_cold_start(user_id, top_n, return_metadata=True)
    recs = [r for r in recs if r.get('origin')]
    recs = variant_engine.enrich(recs, user_id)

    conn = engine.get_db_connection()
    try:
        with conn.cursor() as cursor:
            for r in recs:
                cursor.execute("SELECT name, price FROM products WHERE id = %s", (r["id"],))
                p = cursor.fetchone()
                if p:
                    r["name"] = p["name"]
                    r["price"] = float(p["price"])
                else:
                    r["name"] = f"Produk #{r['id']}"
                    r["price"] = 0
    finally:
        conn.close()

    return {"user_id": user_id, "items": recs}


@app.get("/admin/cf-detail/{user_id}", tags=["Admin"])
def get_cf_calculation_detail(user_id: int, top_n: int = 8):
    """
    Detail perhitungan CF lengkap untuk satu user.
    Menampilkan: sinyal mentah, pivot table, similarity matrix, skor, dan hasil akhir.
    """
    log_request(f"/admin/cf-detail/{user_id}")
    engine.compute_similarity()
    
    result = {
        "user_id": user_id,
        "steps": {}
    }
    
    # ==========================================
    # STEP 0: Parameter Aktif Model (Auto-Tuning v5.4)
    # ==========================================
    params = engine.get_active_params()
    result["steps"]["0_params"] = {
        "title": "Step 0: Parameter Aktif Model (Auto-Tuning)",
        "description": f"Hyperparameter menyesuaikan skala data otomatis — kelas skala saat ini: {params['scale_class']}.",
        "data": [
            {"parameter": "Kelas Skala Data", "nilai": params["scale_class"]},
            {"parameter": "User × Produk (dalam model)", "nilai": f"{params['n_users']} × {params['n_items']}"},
            {"parameter": "Kerapatan Matriks", "nilai": f"{params['density_pct']}%"},
            {"parameter": "Total Sinyal", "nilai": params["total_signals"]},
            {"parameter": "Alpha (bobot User-CF)", "nilai": params["alpha_user_cf"]},
            {"parameter": "Threshold High-Confidence", "nilai": params["base_threshold"]},
            {"parameter": "K tetangga KNN", "nilai": min(params["knn_k"], params["n_users"]) if params["n_users"] else 0},
        ]
    }
    
    # ==========================================
    # STEP 1: Raw Signals (Data Mentah)
    # ==========================================
    df = engine.cache.get("ratings")
    if df is not None and not df.empty:
        user_signals = df[df['user_id'] == user_id][['user_id', 'product_id', 'rating', 'source']].copy()
        user_signals['rating'] = user_signals['rating'].round(2)
        result["steps"]["1_raw_signals"] = {
            "title": "Step 1: Raw Signals (Data Interaksi User)",
            "description": "Data mentah dari tabel ratings, orders, cart, likes, dan product_views setelah pembobotan dan time decay.",
            "total_signals": len(user_signals),
            "data": user_signals.to_dict(orient='records')
        }
    else:
        result["steps"]["1_raw_signals"] = {
            "title": "Step 1: Raw Signals",
            "description": "Tidak ada data interaksi.",
            "total_signals": 0,
            "data": []
        }
    
    # ==========================================
    # STEP 2: Rating Normalization (Mean-Centering)
    # ==========================================
    pivot_centered = engine.cache.get("pivot_centered")
    pivot = engine.cache.get("pivot")
    
    if pivot_centered is not None and user_id in pivot_centered.index:
        user_row_raw = pivot.loc[user_id]
        user_row_norm = pivot_centered.loc[user_id]
        
        conn = engine.get_db_connection()
        try:
            with conn.cursor() as cursor:
                cursor.execute("SELECT id, name FROM products")
                all_pnames = {r['id']: r['name'] for r in cursor.fetchall()}
        finally:
            conn.close()
        
        # STEP 2 Calculation
        user_avg = user_signals['rating'].mean()
        step2_data = []
        
        for product_id, raw_rating in user_row_raw[user_row_raw > 0].items():
            centered_val = raw_rating - user_avg
            step2_data.append({
                "product": all_pnames.get(product_id, f"ID {product_id}"),
                "raw_rating": round(float(raw_rating), 2),
                "user_avg": round(float(user_avg), 2),
                "centered_result": round(float(centered_val), 4)
            })
        
        result["steps"]["2_normalization"] = {
            "title": "Step 2: Rating Normalization (Mean-Centering)",
            "description": "Mengurangi rating dengan rata-rata user — hanya aktif bila sparsity < 85% (kondisional v5.4).",
            "formula": "Normalized(r) = r - average(user_ratings)",
            "data": step2_data
        }
    else:
        result["steps"]["2_normalization"] = {
            "title": "Step 2: Normalization",
            "description": "User belum memiliki data untuk dinormalisasi.",
            "data": []
        }
    
    # ==========================================
    # STEP 3: User-User Similarity Matrix (KNN)
    # ==========================================
    knn_user = engine.cache.get("knn_user")
    if knn_user and pivot_centered is not None and user_id in pivot_centered.index:
        user_vector = pivot_centered.loc[[user_id]].values
        distances, indices = knn_user.kneighbors(user_vector)
        
        conn = engine.get_db_connection()
        try:
            with conn.cursor() as cursor:
                all_uids = [int(pivot_centered.index[idx]) for idx in indices[0]]
                if all_uids:
                    format_ids = ','.join(['%s'] * len(all_uids))
                    cursor.execute(f"SELECT id, username FROM users WHERE id IN ({format_ids})", tuple(all_uids))
                    unames = {r['id']: r['username'] for r in cursor.fetchall()}
                else:
                    unames = {}
        finally:
            conn.close()
        
        sim_data = []
        for i, neighbor_idx in enumerate(indices[0]):
            other_uid = int(pivot_centered.index[neighbor_idx])
            if other_uid == user_id: continue
            score = 1 - distances[0][i]
            sim_data.append({
                "other_user_id": other_uid,
                "other_username": unames.get(other_uid, f"User #{other_uid}"),
                "knn_similarity": round(float(score), 4),
                "similarity_pct": round(float(score) * 100, 1)
            })
        
        result["steps"]["3_user_similarity"] = {
            "title": "Step 3: User-User KNN Similarity",
            "description": f"Mencari tetangga terdekat user {user_id} menggunakan Sparse Matrix KNN. (Metric: Cosine)",
            "formula": "Similarity = 1 - CosineDistance(A, B)",
            "data": sim_data
        }
    else:
        result["steps"]["3_user_similarity"] = {
            "title": "Step 3: User-User Similarity",
            "description": "User belum ada di model (Cold Start).",
            "data": []
        }
    
    # ==========================================
    # STEP 4: Item-Item Similarity (Matrix Neighbors)
    # ==========================================
    item_sim_df = engine.cache.get("item_sim")
    if item_sim_df is not None and pivot_centered is not None and user_id in pivot_centered.index:
        user_rated = pivot.loc[user_id]
        rated_products = user_rated[user_rated > 0].index.tolist()
        
        conn = engine.get_db_connection()
        try:
            with conn.cursor() as cursor:
                cursor.execute("SELECT id, name FROM products")
                all_pnames = {r['id']: r['name'] for r in cursor.fetchall()}
        finally:
            conn.close()
        
        item_sim_data = []
        for pid in rated_products:
            if pid not in item_sim_df.index: continue
            
            # Ambil item paling mirip dari matrix similarity (tanpa batasan untuk simulasi)
            sim_series = item_sim_df[pid].sort_values(ascending=False)
            
            for sim_pid, score in sim_series.items():
                if int(sim_pid) == int(pid): continue 
                
                if float(score) > 0.01:
                    item_sim_data.append({
                        "source_product_id": int(pid),
                        "source_product": all_pnames.get(pid, f"ID {pid}"),
                        "similar_product_id": int(sim_pid),
                        "similar_product": all_pnames.get(sim_pid, f"ID {sim_pid}"),
                        "similarity_score": round(float(score), 4),
                        "user_rating": round(float(user_rated[pid]), 1)
                    })
        
        item_sim_data.sort(key=lambda x: x['similarity_score'], reverse=True)
        
        result["steps"]["4_item_similarity"] = {
            "title": "Step 4: Item-Item Similarity (Shrinkage)",
            "description": f"Produk terdekat menggunakan Cosine Similarity dengan Significance Shrinkage (λ={engine.cache.get('shrinkage_lambda', 3)}).",
            "formula": "FinalSim = CosineSim × CoOccurrence / (CoOccurrence + λ)",
            "data": item_sim_data
        }
    else:
        result["steps"]["4_item_similarity"] = {
            "title": "Step 4: Item-Item Similarity",
            "description": "User belum memiliki produk yang dirating.",
            "data": []
        }
    
    # ==========================================
    # STEP 5: Score Aggregation (Weighted Alpha — Auto-Tuned v5.4)
    # ==========================================
    u_raw_scores = engine._get_user_recs(user_id, top_n * 3)
    i_raw_scores = engine._get_item_recs(user_id, top_n * 3)
    u_norm, i_norm = engine._normalize_scores_global(u_raw_scores, i_raw_scores)
    
    alpha = params["alpha_user_cf"]
    thr = params["base_threshold"]
    
    # FILTER: Hanya ambil produk yang memiliki skor positif (> 0) di salah satu model
    valid_pids = set()
    for pid, score in u_norm.items():
        if score > 0: valid_pids.add(pid)
    for pid, score in i_norm.items():
        if score > 0: valid_pids.add(pid)
        
    score_data = []
    for pid in valid_pids:
        un = u_norm.get(pid, 0)
        inorm = i_norm.get(pid, 0)
        hybrid = un > 0 and inorm > 0
        total = alpha * un + (1 - alpha) * inorm
        
        # Label & confidence mengikuti logika engine v5.4
        if hybrid and total > thr:
            origin, conf = "BEST MATCH", "HIGH"
        elif hybrid:
            origin, conf = "MED MATCH", "MEDIUM"
        elif un > 0:
            origin, conf = "FOR YOU", "USER-ONLY"
        else:
            origin, conf = "STYLE MATCH", "ITEM-ONLY"
            
        score_data.append({
            "product": all_pnames.get(pid, f"ID {pid}"),
            "user_score_0_1": round(float(un), 4),
            "item_score_0_1": round(float(inorm), 4),
            "hybrid": "Ya" if hybrid else "Tidak",
            "final_score": round(float(total), 4),
            "confidence": conf,
            "origin": origin
        })
    
    score_data.sort(key=lambda x: x['final_score'], reverse=True)
    
    result["steps"]["5_aggregation"] = {
        "title": "Step 5: Score Aggregation (Weighted Alpha)",
        "description": (
            f"Skor akhir = {alpha} × UserScore + {round(1 - alpha, 2)} × ItemScore "
            f"(normalisasi per-model terpisah). "
            f"Hybrid dengan skor > {thr} masuk kelas HIGH (sesuai auto-tuning skala data)."
            if score_data else
            f"CF belum menghasilkan kandidat untuk user ini (data terlalu kecil — KNN butuh > 3 user & produk). "
            f"Sistem otomatis memakai popularitas pada Step 6. Parameter aktif: alpha={alpha}, threshold={thr}."
        ),
        "formula": (
            "UserCF: pred = μᵤ + Σ s·(r−μᵥ)/Σ|s| · ItemCF: score = Σ sim·r/Σ sim · "
            f"Final = {alpha}·NormUser + {round(1 - alpha, 2)}·NormItem"
        ),
        "data": score_data
    }
    
    # ==========================================
    # STEP 6: Final Recommendation Output (Elite Top 5 Only)
    # ==========================================
    # Ambil hasil dari engine
    raw_final_recs = engine.get_hybrid_with_cold_start(user_id, 1000, return_metadata=True)
    
    # FILTER: Hanya tampilkan yang memiliki Origin (Elite Only)
    final_recs = [r for r in raw_final_recs if r.get('origin') is not None]
    
    # Lapisan varian v5.4: sarankan warna + ukuran terbaik per user
    final_recs = variant_engine.enrich(final_recs, user_id)
    
    conn = engine.get_db_connection()
    try:
        with conn.cursor() as cursor:
            for r in final_recs:
                cursor.execute("SELECT name, price FROM products WHERE id = %s", (r["id"],))
                p = cursor.fetchone()
                if p:
                    r["name"] = p["name"]
                    r["price"] = float(p["price"])
    finally:
        conn.close()
    
    result["steps"]["6_final_result"] = {
        "title": "Step 6: Mixed Hybrid + Lapisan Varian",
        "description": f"Hasil akhir: Rekomendasi Personal (Uncapped) + Produk Populer, masing-masing dilengkapi varian warna & ukuran terbaik untuk user.",
        "data": final_recs
    }
    
    return result

@app.post("/cache/refresh", tags=["Admin"])
def refresh_cache():
    """Force refresh cache model CF + cache varian."""
    engine.fetch_enhanced_ratings(force=True)
    engine.compute_similarity()
    variant_engine.invalidate()
    return {"status": "success", "message": "Cache refreshed"}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8000)
