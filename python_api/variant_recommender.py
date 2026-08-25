"""
JI-DOOR VARIANT RECOMMENDER v1.0
================================
Lapisan 2 dari sistem rekomendasi: memilih VARIAN terbaik (warna + ukuran)
untuk setiap produk hasil ranking CF (Lapisan 1).

Logika skor varian per user:
    skor = kecocokan warna (riwayat user) x 1.2
         + kecocokan ukuran (riwayat user) x 1.0
         + popularitas global varian        x 0.6 (dibatasi)
         + ketersediaan stok                x 0.1

Hanya varian dengan stok > 0 yang direkomendasikan.
Sinyal personal berasal dari:
    - order_items.color / .size  (pembelian, bobot 3/baris)
    - cart -> product_variants   (minat aktif, bobot 2/baris)

Fallback: varian terpopuler produk tersebut (berdasarkan penjualan global),
lalu stok terbesar — sehingga kartu rekomendasi selalu punya saran varian
walaupun user baru (cold start).
"""

import time
import pymysql
import os
import logging
from collections import Counter
from typing import List, Dict, Any, Optional

logger = logging.getLogger(__name__)

# Nilai varian yang dianggap "tanpa preferensi"
NEUTRAL_TOKENS = {"", "standar", "-", "default"}


class VariantRecommender:
    def __init__(self, db_config: Dict[str, Any]):
        self.db_config = db_config
        self.ttl = 60
        self._profile_cache: Dict[int, Dict[str, Any]] = {}
        self._popular_cache: Optional[Dict[int, int]] = None
        self._popular_ts: float = 0

    # ----------------------------------------------------------
    # DATABASE
    # ----------------------------------------------------------
    def get_db_connection(self):
        config = self.db_config.copy()
        mamp_socket = "/Applications/MAMP/tmp/mysql/mysql.sock"
        if os.path.exists(mamp_socket):
            config["unix_socket"] = mamp_socket
            config.pop("host", None)
            config.pop("port", None)
        return pymysql.connect(**config)

    @staticmethod
    def _norm(value: Optional[str]) -> str:
        """Normalisasi nilai warna/ukuran; 'Standar'/'-'/'NULL' = netral."""
        return (value or "").strip().lower() if value else ""

    # ----------------------------------------------------------
    # PROFIL PREFERENSI USER
    # ----------------------------------------------------------
    def get_user_profile(self, user_id: int) -> Dict[str, Counter]:
        """Hitung seberapa sering user membeli/memasukkan varian warna & ukuran tertentu."""
        now = time.time()
        cached = self._profile_cache.get(user_id)
        if cached and (now - cached["ts"]) < self.ttl:
            return cached["data"]

        colors: Counter = Counter()
        sizes: Counter = Counter()

        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                # Pembelian (sinyal tertinggi, bobot 3) — pesanan aktif saja
                cursor.execute(
                    """SELECT oi.color, oi.size, SUM(oi.qty) AS q
                       FROM order_items oi
                       JOIN orders o ON o.id = oi.order_id
                       WHERE o.user_id = %s AND o.status != 'cancelled'
                       GROUP BY oi.color, oi.size""",
                    (user_id,),
                )
                for row in cursor.fetchall():
                    c, s, q = self._norm(row["color"]), self._norm(row["size"]), int(row["q"] or 1)
                    if c and c not in NEUTRAL_TOKENS:
                        colors[c] += 3 * q
                    if s and s not in NEUTRAL_TOKENS:
                        sizes[s] += 3 * q

                # Keranjang (minat aktif, bobot 2)
                cursor.execute(
                    """SELECT v.color, v.size, SUM(c.qty) AS q
                       FROM cart c
                       JOIN product_variants v ON v.id = c.variant_id
                       WHERE c.user_id = %s
                       GROUP BY v.color, v.size""",
                    (user_id,),
                )
                for row in cursor.fetchall():
                    c, s, q = self._norm(row["color"]), self._norm(row["size"]), int(row["q"] or 1)
                    if c and c not in NEUTRAL_TOKENS:
                        colors[c] += 2 * q
                    if s and s not in NEUTRAL_TOKENS:
                        sizes[s] += 2 * q
        finally:
            conn.close()

        data = {"colors": colors, "sizes": sizes}
        self._profile_cache[user_id] = {"data": data, "ts": now}
        return data

    # ----------------------------------------------------------
    # POPULARITAS GLOBAL VARIAN
    # ----------------------------------------------------------
    def get_global_popularity(self) -> Dict[int, int]:
        """Total qty penjualan per variant_id (pesanan non-cancelled)."""
        now = time.time()
        if self._popular_cache is not None and (now - self._popular_ts) < self.ttl:
            return self._popular_cache

        popular: Dict[int, int] = {}
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                cursor.execute(
                    """SELECT oi.variant_id, SUM(oi.qty) AS q
                       FROM order_items oi
                       JOIN orders o ON o.id = oi.order_id
                       WHERE o.status != 'cancelled' AND oi.variant_id IS NOT NULL
                       GROUP BY oi.variant_id"""
                )
                for row in cursor.fetchall():
                    popular[int(row["variant_id"])] = int(row["q"])
        finally:
            conn.close()

        self._popular_cache = popular
        self._popular_ts = now
        return popular

    # ----------------------------------------------------------
    # PEMILIHAN VARIAN
    # ----------------------------------------------------------
    def pick_for_product(
        self,
        product_id: int,
        profile: Dict[str, Counter],
        popular: Dict[int, int],
    ) -> Optional[Dict[str, Any]]:
        """Pilih varian terbaik (stok > 0) untuk satu produk."""
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                cursor.execute(
                    """SELECT id, color, size, stock
                       FROM product_variants
                       WHERE product_id = %s AND stock > 0""",
                    (product_id,),
                )
                variants = cursor.fetchall()
        finally:
            conn.close()

        if not variants:
            return None

        max_pop = max(popular.values()) if popular else 0
        best, best_score = None, -1.0

        for v in variants:
            c, s = self._norm(v["color"]), self._norm(v["size"])
            vid = int(v["id"])

            score = 0.0
            if c and c not in NEUTRAL_TOKENS:
                score += 1.2 * min(profile["colors"].get(c, 0), 9) / 9.0
            if s and s not in NEUTRAL_TOKENS:
                score += 1.0 * min(profile["sizes"].get(s, 0), 9) / 9.0

            pop = min(popular.get(vid, 0), 10)
            if max_pop > 0:
                score += 0.6 * (pop / max(max_pop, 10))

            score += 0.1 * min(int(v["stock"]), 10) / 10.0

            if score > best_score:
                best, best_score = v, score

        if best is None:
            best = variants[0]

        return {
            "id": int(best["id"]),
            "color": best["color"],
            "size": best["size"],
        }

    # ----------------------------------------------------------
    # ENRICHMENT
    # ----------------------------------------------------------
    def enrich(self, recs: List[Dict[str, Any]], user_id: int) -> List[Dict[str, Any]]:
        """
        Lampirkan varian terbaik ke setiap item rekomendasi:
        {"id": 5, "origin": "BEST MATCH"} ->
        {"id": 5, "origin": "BEST MATCH", "variant": {"id": 88, "color": "Hitam", "size": "L"}}
        """
        if not recs:
            return recs

        profile = self.get_user_profile(user_id) if user_id and user_id > 0 else {"colors": Counter(), "sizes": Counter()}
        popular = self.get_global_popularity()

        for r in recs:
            try:
                r["variant"] = self.pick_for_product(int(r["id"]), profile, popular)
            except Exception as e:
                logger.warning(f"Gagal pilih varian utk produk {r.get('id')}: {e}")
                r["variant"] = None
        return recs

    def invalidate(self):
        """Bersihkan semua cache internal (dipanggil dari /cache/refresh)."""
        self._profile_cache.clear()
        self._popular_cache = None
        self._popular_ts = 0
