#!/usr/bin/env python3
"""
Loader data dummy JiDoor Store v5.5 (idempotent).

Tujuan: mencukupi syarat evaluasi CF — >= 5 user aktif, >= 10 sampel uji —
sekaligus memunculkan pola User-KNN & Item-CF dengan 3 klaster preferensi:
Kasual (Kaos/Polo/Rompi/Lanyard/Topi), Formal (PDL/Setelan/Jaket), Campuran.

PENTING: tanggal interaksi dibuat RECENT (<= 30 hari dari "sekarang") agar
time-decay engine (factor 1.0 / 0.8 / 0.5) TIDAK menghancurkan rating.
Run: ./venv/bin/python sql/load_dummy.py
"""
import pymysql
from datetime import datetime, timedelta

DB = {
    "host": "127.0.0.1", "port": 8889, "user": "root",
    "password": "root", "database": "ecommerce_db",
    "cursorclass": pymysql.cursors.DictCursor,
}

PW = "$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT."  # admin123

DUMMY_IDS = list(range(200, 210))
ORDER_IDS = list(range(20, 38))
NOW = datetime(2026, 8, 25, 0, 0, 0)


def d(days_ago, hh=0, mm=0):
    return (NOW - timedelta(days=days_ago)).replace(hour=hh, minute=mm, second=0)


def main():
    conn = pymysql.connect(**DB)
    cur = conn.cursor()

    # --- Cleanup (idempotent) ---
    for uid in DUMMY_IDS:
        cur.execute("DELETE FROM product_views WHERE user_id = %s", (uid,))
        cur.execute("DELETE FROM cart WHERE user_id = %s", (uid,))
        cur.execute("DELETE FROM likes WHERE user_id = %s", (uid,))
        cur.execute("DELETE FROM ratings WHERE user_id = %s", (uid,))
        cur.execute("DELETE FROM orders WHERE user_id = %s", (uid,))
        cur.execute("DELETE FROM users WHERE id = %s", (uid,))
    for oid in ORDER_IDS:
        cur.execute("DELETE FROM order_items WHERE order_id = %s", (oid,))
        cur.execute("DELETE FROM orders WHERE id = %s", (oid,))
    # Rating dummy asep (119) — produk 322 & 328 ditambahkan oleh loader ini
    cur.execute("DELETE FROM ratings WHERE user_id = 119 AND product_id IN (322, 328)")

    # --- Users ---
    users = [
        (200, 'budi',   'budi@example.com',   'Jl. Merdeka 1, Bandung',    d(40)),
        (201, 'deni',   'deni@example.com',   'Jl. Asia Afrika 5, Bandung', d(38)),
        (202, 'rina',   'rina@example.com',   'Jl. Buah Batu 12, Bandung',  d(36)),
        (203, 'wati',   'wati@example.com',   'Jl. Dago 8, Bandung',        d(34)),
        (204, 'eko',    'eko@example.com',    'Jl. Gatot Subroto 20, Jakarta', d(32)),
        (205, 'maya',   'maya@example.com',   'Jl. Sudirman 33, Jakarta',   d(30)),
        (206, 'lia',    'lia@example.com',    'Jl. Thamrin 15, Jakarta',    d(28)),
        (207, 'tommy',  'tommy@example.com',  'Jl. Rasuna Said 7, Jakarta', d(26)),
        (208, 'sari',   'sari@example.com',   'Jl. Pemuda 25, Surabaya',    d(24)),
        (209, 'yanto',  'yanto@example.com',  'Jl. Malioboro 4, Yogyakarta', d(22)),
    ]
    cur.executemany(
        "INSERT INTO users (id, username, email, password, role, phone, address, created_at) "
        "VALUES (%s,%s,%s,%s,'user','0812300002'+LPAD(%s,2,'0'),%s,%s)",
        [(u[0], u[1], u[2], PW, str(u[0])[-2:], u[3], u[4]) for u in users],
    )

    # --- Ratings (explicit, recent) ---
    # (user, product, rating) — pola klaster
    ratings = [
        # Kasual
        (200, 322, 4, 20), (200, 323, 5, 18), (200, 324, 4, 15), (200, 326, 3, 12),
        (201, 322, 5, 19), (201, 324, 4, 16), (201, 329, 5, 13),
        (202, 323, 5, 21), (202, 328, 4, 14), (202, 329, 3, 10),
        (203, 322, 4, 17), (203, 326, 4, 11), (203, 328, 5, 8),
        # Formal
        (204, 325, 5, 18), (204, 330, 5, 15), (204, 327, 4, 9),
        (205, 325, 5, 20), (205, 330, 4, 14), (205, 327, 4, 7),
        (206, 325, 4, 16), (206, 330, 5, 12), (206, 327, 3, 6),
        (207, 325, 5, 17), (207, 330, 4, 10), (207, 327, 5, 4),
        # Campuran
        (208, 322, 3, 19), (208, 325, 4, 15), (208, 327, 4, 8), (208, 330, 3, 3),
        (209, 323, 4, 18), (209, 328, 5, 13), (209, 329, 4, 9), (209, 326, 3, 2),
        # asep (tambah)
        (119, 322, 4, 6), (119, 328, 5, 5),
    ]
    cur.executemany(
        "INSERT INTO ratings (user_id, product_id, rating, review, created_at, updated_at) "
        "VALUES (%s,%s,%s,'dummy',%s,%s)",
        [(r[0], r[1], r[2], d(r[3], 10), d(r[3], 10)) for r in ratings],
    )

    # --- Orders + items ---
    # (order_id, user, [(product, color, size, qty, price), ...], days_ago)
    orders = [
        (20, 200, [(322, 'Hitam', 'L', 1, 80000), (324, 'Navy', 'M', 1, 75000)], 19),
        (21, 200, [(323, 'Biru Muda', 'L', 1, 75000), (329, 'Hitam', 'Standar', 1, 60000)], 12),
        (22, 201, [(322, 'Putih', 'M', 1, 80000), (329, 'Navy', 'Standar', 1, 70000)], 18),
        (23, 201, [(323, 'Cream', 'L', 1, 75000)], 6),
        (24, 202, [(323, 'Abu-abu', 'M', 1, 75000)], 15),
        (25, 202, [(328, 'Hitam', 'Standar', 1, 45000), (329, 'Cream', 'Standar', 1, 75000)], 9),
        (26, 203, [(322, 'Maroon', 'S', 1, 80000), (326, 'Navy', 'M', 1, 60000)], 11),
        (27, 204, [(325, 'Hitam', 'L', 1, 180000), (330, 'Hitam', 'L', 1, 150000)], 17),
        (28, 204, [(327, 'Navy', 'XL', 1, 180000)], 8),
        (29, 205, [(325, 'Navy', 'M', 1, 180000), (330, 'Cream', 'M', 1, 180000)], 16),
        (30, 205, [(330, 'Hitam', 'M', 1, 150000)], 7),
        (31, 206, [(325, 'Hijau Army', 'L', 1, 180000), (330, 'Putih', 'L', 1, 150000)], 14),
        (32, 206, [(327, 'Hitam', 'L', 1, 180000)], 5),
        (33, 207, [(325, 'Hitam', 'XL', 1, 180000), (330, 'Navy', 'XL', 1, 150000), (327, 'Cream', 'XL', 1, 180000)], 13),
        (34, 208, [(325, 'Hitam', 'M', 1, 180000), (327, 'Navy', 'M', 1, 180000)], 12),
        (35, 208, [(322, 'Kuning', 'L', 1, 80000)], 4),
        (36, 209, [(326, 'Hitam', 'M', 1, 60000)], 10),
        (37, 209, [(323, 'Merah', 'L', 1, 75000), (328, 'Navy', 'Standar', 1, 45000)], 3),
    ]
    for oid, uid, items, days in orders:
        total = sum(x[4] for x in items)
        cur.execute(
            "INSERT INTO orders (id, user_id, receiver_name, phone, total_price, status, address, created_at) "
            "VALUES (%s,%s,%s,'0812300002XX',%s,'delivered','Jl. Dummy %s',%s)",
            (oid, uid, f"User{uid}", total, uid, d(days, 9)),
        )
        for (pid, color, size, qty, price) in items:
            cur.execute(
                "INSERT INTO order_items (order_id, product_id, color, size, qty, price) "
                "VALUES (%s,%s,%s,%s,%s,%s)",
                (oid, pid, color, size, qty, price),
            )

    # --- Likes ---
    likes = [
        (200, 322, 20), (200, 324, 15), (200, 329, 12),
        (201, 322, 19), (201, 323, 16),
        (202, 328, 21), (202, 329, 10),
        (203, 326, 17), (203, 328, 8),
        (204, 325, 18), (204, 330, 15),
        (205, 327, 20), (205, 330, 14),
        (206, 325, 16), (206, 330, 12),
        (207, 325, 17), (207, 327, 10),
        (208, 322, 19), (208, 325, 15),
        (209, 323, 18),
    ]
    cur.executemany(
        "INSERT INTO likes (user_id, product_id, created_at) VALUES (%s,%s,%s)",
        [(l[0], l[1], d(l[2], 14)) for l in likes],
    )

    # --- Cart ---
    cart = [
        (200, 324, 695, 3), (200, 326, 766, 2),
        (201, 329, 1186, 2), (201, 326, 774, 1),
        (202, 322, 555, 3), (202, 328, 868, 1),
        (203, 323, 613, 2), (203, 324, 669, 1),
        (204, 327, 837, 1), (204, 330, 1128, 2),
        (205, 325, 719, 1), (205, 330, 1127, 2),
        (206, 325, 714, 2), (206, 327, 833, 1),
        (207, 325, 721, 1), (207, 330, 1131, 1),
        (208, 326, 773, 1), (208, 330, 1129, 1),
        (209, 322, 554, 2), (209, 329, 1189, 1),
    ]
    cur.executemany(
        "INSERT INTO cart (user_id, product_id, variant_id, qty, note, custom_text, created_at) "
        "VALUES (%s,%s,%s,%s,'','',%s)",
        [(c[0], c[1], c[2], c[3], d(c[3], 11)) for c in cart],
    )

    # --- Views (some repeats to test cap) ---
    views = [
        # user, product, days_ago, session
        (200, 322, 20, 's200-1'), (200, 322, 19, 's200-2'), (200, 322, 18, 's200-3'),
        (200, 324, 17, 's200-4'), (200, 324, 16, 's200-5'), (200, 329, 15, 's200-6'),
        (200, 326, 14, 's200-7'), (200, 328, 13, 's200-8'),
        (201, 322, 19, 's201-1'), (201, 324, 16, 's201-2'), (201, 324, 15, 's201-3'),
        (201, 329, 13, 's201-4'), (201, 329, 12, 's201-5'), (201, 326, 10, 's201-6'),
        (201, 323, 9, 's201-7'),
        (202, 323, 21, 's202-1'), (202, 323, 20, 's202-2'), (202, 328, 14, 's202-3'),
        (202, 328, 13, 's202-4'), (202, 329, 10, 's202-5'), (202, 322, 8, 's202-6'),
        (203, 322, 17, 's203-1'), (203, 326, 15, 's203-2'), (203, 328, 10, 's203-3'),
        (203, 328, 9, 's203-4'), (203, 329, 7, 's203-5'), (203, 324, 5, 's203-6'),
        (204, 325, 18, 's204-1'), (204, 325, 17, 's204-2'), (204, 330, 15, 's204-3'),
        (204, 327, 10, 's204-4'), (204, 327, 9, 's204-5'), (204, 330, 7, 's204-6'),
        (205, 325, 20, 's205-1'), (205, 325, 19, 's205-2'), (205, 330, 14, 's205-3'),
        (205, 330, 13, 's205-4'), (205, 327, 10, 's205-5'), (205, 327, 8, 's205-6'),
        (206, 325, 16, 's206-1'), (206, 325, 15, 's206-2'), (206, 330, 12, 's206-3'),
        (206, 327, 6, 's206-4'), (206, 327, 5, 's206-5'),
        (207, 325, 17, 's207-1'), (207, 325, 16, 's207-2'), (207, 330, 11, 's207-3'),
        (207, 327, 7, 's207-4'), (207, 327, 6, 's207-5'),
        (208, 322, 19, 's208-1'), (208, 325, 15, 's208-2'), (208, 327, 12, 's208-3'),
        (208, 330, 9, 's208-4'), (208, 326, 6, 's208-5'), (208, 323, 3, 's208-6'),
        (209, 323, 18, 's209-1'), (209, 328, 13, 's209-2'), (209, 328, 12, 's209-3'),
        (209, 329, 9, 's209-4'), (209, 326, 5, 's209-5'), (209, 322, 3, 's209-6'),
        (209, 324, 2, 's209-7'),
    ]
    cur.executemany(
        "INSERT INTO product_views (user_id, product_id, duration_seconds, view_date, created_at, session_id) "
        "VALUES (%s,%s,30,%s,%s,%s)",
        [(v[0], v[1], d(v[2]).date(), d(v[2], 10), v[3]) for v in views],
    )

    conn.commit()

    # --- Summary ---
    for t in ['users', 'ratings', 'orders', 'order_items', 'likes', 'cart', 'product_views']:
        cur.execute(f"SELECT COUNT(*) c FROM {t}")
        print(f"{t}: {cur.fetchone()['c']}")
    conn.close()
    print("DUMMY DATA LOADED")


if __name__ == "__main__":
    main()
