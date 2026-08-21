# 🚪 JiDoor E-Commerce + AI Recommendation Engine v5.3

JiDoor adalah platform e-commerce premium yang dilengkapi dengan **Pure Collaborative Filtering Engine** untuk memberikan pengalaman belanja yang sangat personal.

---

## 📁 Struktur Proyek

```
new-jidoor-ecommerce/
├── application/             ← CodeIgniter 3 (Business Logic)
├── assets/                  ← CSS, JS, & Media
├── uploads/                 ← Image Storage (Products & Payments)
├── python_api/              ← AI Recommendation Engine (FastAPI)
│   ├── recommendation_engine.py  ← Core AI Logic
│   ├── main.py                   ← API & Admin Simulation
│   └── requirements.txt
├── ecommerce_db.sql         ← Database Schema
└── DOCUMENTATION_AI_ENGINE.md ← Advanced Technical Docs
```

---

## 🚀 Quick Start

### 1. Database Setup

Impor `ecommerce_db.sql` melalui phpMyAdmin atau terminal:

```bash
/Applications/MAMP/Library/bin/mysql -u root -proot ecommerce_db < ecommerce_db.sql
```

### 2. Jalankan Frontend (PHP/CI3)

- Pastikan Apache & MySQL di MAMP menyala.
- Akses: `http://localhost:8888/new-jidoor-ecommerce/`

### 3. Jalankan AI Engine (Python)

```bash
cd python_api
pip install -r requirements.txt
uvicorn main:app --host 0.0.0.0 --port 8000 --reload
```

---

## 👤 Akun Default

| Role  | Username     | Password    |
| ----- | ------------ | ----------- |
| Admin | admin        | admin123    |
| User  | budi_santoso | password123 |

---

## **Dokumentasi Teknis Lengkap**

---

## 📋 **Daftar Isi**

1. [Ringkasan Eksekutif](#ringkasan-eksekutif)
2. [Arsitektur Sistem](#arsitektur-sistem)
3. [Core Algorithm](#core-algorithm)
4. [Data Processing Pipeline](#data-processing-pipeline)
5. [Similarity Computation](#similarity-computation)
6. [Recommendation Generation](#recommendation-generation)
7. [Cold Start Handling](#cold-start-handling)
8. [API Endpoints](#api-endpoints)
9. [Database Schema](#database-schema)
10. [Configuration & Tuning](#configuration--tuning)
11. [Performance Metrics](#performance-metrics)
12. [Troubleshooting](#troubleshooting)

---

## 🎯 **Ringkasan Eksekutif**

### **Apa itu JiDoor CF Engine?**

Mesin rekomendasi berbasis **Collaborative Filtering (CF)** murni yang merekomendasikan produk berdasarkan perilaku pengguna lain yang相似, bukan berdasarkan konten produk.

### **Versi:** 5.3

### **Tanggal Rilis:** 2026-04-30

### **Teknologi:** Python, FastAPI, Scikit-learn, Pandas, NumPy, MySQL

### **Fitur Utama:**

| Fitur                     | Deskripsi                                     |
| ------------------------- | --------------------------------------------- |
| **User-Based CF**         | Mencari pengguna dengan pola perilaku serupa  |
| **Item-Based CF**         | Mencari produk yang sering muncul bersama     |
| **Hybrid Scoring**        | Menggabungkan kedua metode dengan bobot 60:40 |
| **Cold Start Handling**   | Strategi khusus untuk user/produk baru        |
| **Multi-Signal Rating**   | Menggabungkan 5 jenis sinyal interaksi        |
| **Time Decay**            | Data lama memiliki pengaruh lebih kecil       |
| **Co-occurrence Penalty** | Mengurangi noise pada data sparse             |

---

## 🏗️ **Arsitektur Sistem**

### **High-Level Architecture**

```
┌─────────────────────────────────────────────────────────────────┐
│                         DATABASE LAYER                          │
│  ┌─────────┐ ┌─────────┐ ┌───────┐ ┌──────────┐ ┌────────────┐  │
│  │ Ratings │ │ Orders  │ │ Cart  │ │Wishlists │ │ProductViews│  │
│  └────┬────┘ └────┬────┘ └───┬───┘ └────┬─────┘ └──────┬─────┘  │
└───────┼───────────┼──────────┼──────────┼──────────────┼────────┘
        │           │          │          │              │
        └───────────┴──────────┴──────────┴──────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────┐
│                    DATA COLLECTION LAYER                        │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │         fetch_enhanced_ratings()                         │   │
│  │  - Weighted signals (purchase=5.0, view=1.5, etc)        │   │
│  │  - Time decay (7d=1.0, 30d=0.8, 90d=0.5, >90d=0.3)       │   │
│  │  - Deduplication & clipping                              │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────┐
│                      CF CORE ENGINE                             │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │           compute_similarity()                           │   │
│  │  - Conditional Mean-Centering (sparsity < 85%)           │   │
│  │  - Cosine Similarity matrix                              │   │
│  │  - Co-occurrence penalty (clip(co_matrix/3, 0.2, 1.0))   │   │
│  │  - KNN for fast neighbor lookup                          │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────┐
│                   RECOMMENDATION LAYER                          │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │  User-Based CF  │  │  Item-Based CF  │  │ Popular Fallback│  │
│  │  _get_user_recs │  │  _get_item_recs │  │ get_popular_ids │  │
│  └────────┬────────┘  └────────┬────────┘  └────────┬────────┘  │
│           └────────────────────┼────────────────────┘           │
│                                ▼                                │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │           get_hybrid_recs()                              │   │
│  │  - Global score normalization                            │   │
│  │  - Alpha weighting (60% User, 40% Item)                  │   │
│  │  - Dynamic threshold (0.55 normal, 0.65 explore)         │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────┐
│                      API LAYER (FastAPI)                        │
│  ┌───────────────┐ ┌───────────────┐ ┌───────────────────────┐  │
│  │ /recommend/   │ │ /admin/stats/ │ │ /admin/cf-detail/     │  │
│  │ {user_id}     │ │               │ │ {user_id}             │  │
│  └───────────────┘ └───────────────┘ └───────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### **Class Diagram**

```python
class RecommendationEngine:
    # Cache
    cache: Dict = {
        "ratings": pd.DataFrame,      # Multi-signal rating matrix
        "user_sim": pd.DataFrame,     # User-User similarity matrix
        "item_sim": pd.DataFrame,     # Item-Item similarity matrix
        "pivot": pd.DataFrame,        # User-Item pivot table
        "pivot_centered": pd.DataFrame, # Mean-centered pivot
        "knn_user": NearestNeighbors, # KNN model for users
        "knn_item": NearestNeighbors, # KNN model for items
        "signature": str,             # DB state signature
        "timestamp": float            # Cache timestamp
    }

    # Core Methods
    + fetch_enhanced_ratings() -> pd.DataFrame
    + compute_similarity() -> None
    + get_hybrid_recs() -> List[Dict]
    + get_hybrid_with_cold_start() -> List[Dict]
    + get_sectioned_recs() -> List[Dict]

    # Helper Methods
    - _get_user_recs() -> Dict[int, float]
    - _get_item_recs() -> Dict[int, float]
    - _normalize_scores_global() -> Tuple[Dict, Dict]
    - get_popular_ids() -> List[int]
    - get_most_viewed_ids() -> List[int]
    - get_best_sellers() -> List[int]
```

---

## 🧠 **Core Algorithm**

### **1. Collaborative Filtering Formula**

#### **User-Based CF**

```
Score(u, i) = Σ sim(u, u') × r(u', i)
```

Dimana:

- `u` = target user
- `u'` = neighbor user
- `sim(u, u')` = cosine similarity antara user
- `r(u', i)` = rating yang diberikan user u' ke produk i

#### **Item-Based CF**

```
Score(u, i) = Σ sim(i, j) × r(u, j)
```

Dimana:

- `i` = target product
- `j` = product yang sudah dirating user
- `sim(i, j)` = cosine similarity antara produk
- `r(u, j)` = rating user ke produk j

#### **Hybrid Score (dengan Alpha Weight)**

```
FinalScore(u, i) = α × UserScore(u, i) + (1-α) × ItemScore(u, i)
```

- **α = 0.6** (60% bobot User-Based, 40% Item-Based)

#### **Global Normalization**

```
Normalized(v) = (v - min_all) / (max_all - min_all)
```

- Normalisasi dilakukan GLOBAL terhadap semua skor (User + Item)
- Menghasilkan range 0.0 - 1.0 yang sebanding

### **2. Cosine Similarity**

```
cosine_similarity(A, B) = (A · B) / (||A|| × ||B||)
```

**Implementasi:**

```python
from sklearn.metrics.pairwise import cosine_similarity
user_sim = cosine_similarity(pivot_centered)
item_sim = cosine_similarity(pivot_centered.T)
```

### **3. Conditional Mean-Centering**

```python
if sparsity < 0.85:  # Data tidak terlalu sparse
    user_mean = pivot.mean(axis=1)
    pivot_centered = pivot.sub(user_mean, axis=0).fillna(0)
else:  # Data sangat sparse > 85%
    pivot_centered = pivot.fillna(0)  # Hindari zero vectors
```

**Tujuan:** Menyeimbangkan user yang 'pelit' (selalu kasih rating rendah) vs 'royal' (selalu kasih rating tinggi)

### **4. Co-occurrence Penalty**

```python
binary_pivot = (pivot_raw > 0).astype(int)
co_matrix = np.dot(binary_pivot.T, binary_pivot)
penalty = np.clip(co_matrix / 3.0, 0.2, 1.0)
item_sim = item_sim * penalty
```

**Logika:**

- Jika dua produk hanya muncul bersama di 1 user → penalty = 0.33
- Jika 2 user → penalty = 0.66
- Jika ≥3 user → penalty = 1.0 (no penalty)

---

## 📊 **Data Processing Pipeline**

### **Signal Weighting (Multi-Signal Rating)**

| Source              | Base Weight | Description                    |
| ------------------- | ----------- | ------------------------------ |
| **Purchase**        | 5.0         | Sinyal terkuat (user membayar) |
| **Explicit Rating** | 1-5         | Rating langsung dari user      |
| **Wishlist**        | 2.5         | Minat pasif, ingin memiliki    |
| **Cart**            | 2.0         | Minat aktif, siap beli         |
| **Product View**    | 1.5         | Sinyal lemah, hanya melihat    |

### **Time Decay Function**

```python
def get_decay_factor(days):
    if days <= 7:    return 1.0   # 1 minggu terakhir
    if days <= 30:   return 0.8   # 1 bulan terakhir
    if days <= 90:   return 0.5   # 3 bulan terakhir
    return 0.3                     # lebih dari 3 bulan
```

**Final Rating = Base Weight × Decay Factor**

### **Data Deduplication Priority**

```
Priority (tinggi ke rendah):
1. Explicit Rating (rank=4)
2. Purchase (rank=3)
3. Wishlist (rank=2)
4. Cart (rank=1)
5. View (rank=0)
```

### **Rating Clipping**

```
Final Rating = clip(rating, 0.5, 5.0)
```

---

## 🔢 **Similarity Computation**

### **Step-by-Step Process**

```
Step 1: Create Pivot Table
┌─────────┬──────────┬──────────┬──────────┐
│ user_id │ prod_245 │ prod_243 │ prod_287 │
├─────────┼──────────┼──────────┼──────────┤
│ 119     │ 5.0      │ 5.0      │ 4.0      │
│ 120     │ 0.0      │ 4.5      │ 0.0      │
│ 121     │ 4.5      │ 0.0      │ 5.0      │
└─────────┴──────────┴──────────┴──────────┘

Step 2: Conditional Mean-Centering
- Hitung user_avg = rata-rata per user
- Kurangi setiap rating dengan user_avg

Step 3: Cosine Similarity
- Hitung similarity matrix antara semua produk

Step 4: Co-occurrence Penalty
- Hitung co_matrix (berapa user yang punya kedua produk)
- penalty = clip(co_matrix/3, 0.2, 1.0)
- item_sim = item_sim × penalty

Step 5: Save to Cache
- Simpan matrix untuk reuse (TTL: 60 detik)
```

### **Memory Optimization**

```python
# Menggunakan Sparse Matrix untuk pivot besar
pivot_sparse = csr_matrix(pivot_centered.values)

# KNN dengan brute force untuk akurasi
knn = NearestNeighbors(n_neighbors=16, metric='cosine', algorithm='brute')
```

---

## 🎯 **Recommendation Generation**

### **Hybrid Recommendation Flow**

```python
def get_hybrid_recs(user_id, top_n=8, explore_mode=False):
    # 1. Get raw scores from both models
    u_raw = _get_user_recs(user_id, top_n=100)
    i_raw = _get_item_recs(user_id, top_n=100)

    # 2. Global normalization (0.0 - 1.0)
    u_norm, i_norm = _normalize_scores_global(u_raw, i_raw)

    # 3. Identify hybrid products (appear in both)
    hybrid_ids = set(u_norm.keys()) & set(i_norm.keys())

    # 4. Calculate hybrid scores
    alpha = 0.6  # User preference weight
    for pid in hybrid_ids:
        score = alpha * u_norm[pid] + (1-alpha) * i_norm[pid]

    # 5. Dynamic thresholding
    high_threshold = 0.65 if explore_mode else 0.55
    high_conf = [pid for pid in hybrid_ids if score > high_threshold]

    # 6. Distribution based on mode
    if explore_mode:
        # 60% exploration, 40% exploitation
        add_to_selected(remaining_candidates, top_n * 3 // 5)
    else:
        # 50% high confidence, 25% medium, 25% others
        add_to_selected(high_conf, top_n // 2)
        add_to_selected(med_conf, top_n // 4)
        add_to_selected(remaining_candidates, top_n // 4)

    # 7. Fallback if needed
    if len(selected) < top_n:
        add_to_selected(most_viewed, top_n - len(selected))

    return selected
```

### **Score Normalization Types**

| Method                       | Range     | Use Case                               |
| ---------------------------- | --------- | -------------------------------------- |
| `_normalize_scores_global()` | 0.0 - 1.0 | Hybrid scoring (User + Item bersama)   |
| `_normalize_scores()`        | 0.0 - 1.0 | Single model scoring (backward compat) |

### **Origin Labels**

| Label           | Meaning                     | Source                  |
| --------------- | --------------------------- | ----------------------- |
| **BEST MATCH**  | Rekomendasi terbaik         | Hybrid (User + Item CF) |
| **FOR YOU**     | Berdasarkan preferensi Anda | User-Based CF only      |
| **STYLE MATCH** | Mirip dengan produk favorit | Item-Based CF only      |
| **HOT HITS**    | Paling banyak dilihat       | Most Viewed (Top 5)     |
| **BEST SELLER** | Paling banyak terjual       | Best Sellers (Top 5)    |
| **TRENDING**    | Skor rating tertinggi       | Popular (Top 5)         |

---

## ❄️ **Cold Start Handling**

### **Cold Start User Strategy**

User baru (tanpa riwayat interaksi) mendapat rekomendasi dari:

```
┌─────────────────────────────────────────────┐
│         COLD START RECOMMENDATIONS          │
├─────────────────────────────────────────────┤
│ 30% Hot Hits     → Most Viewed (Top 5)      │
│ 30% Trending     → Popular (Top 5)          │
│ 20% Context      → Based on session         │
│ 20% New Arrivals → Fresh products           │
│ (Fallback)       → Best Sellers (Top 5)     │
└─────────────────────────────────────────────┘
```

### **Session Context**

```python
def _get_session_context(session_id):
    # Ambil kategori yang dilihat dalam 1 jam terakhir
    viewed_categories = SELECT category_id, COUNT(*)
                       FROM product_views
                       WHERE session_id = ?
                       AND created_at > NOW() - INTERVAL 1 HOUR
                       GROUP BY category_id
                       LIMIT 3

    # Ambil produk yang dilihat
    viewed_products = SELECT product_id
                      FROM product_views
                      WHERE session_id = ?
                      ORDER BY created_at DESC
                      LIMIT 10
```

### **Warm Start User Strategy**

User dengan interaksi < 5 (warm start):

```python
if interaction_count < 5:
    # 60% Collaborative Filtering
    cf_recs = get_hybrid_recs(user_id, top_n=int(limit * 0.6))

    # 40% Cold Start Strategy
    cold_recs = get_cold_start_recs(user_id, limit=int(limit * 0.4))

    # Gabungkan dan deduplikasi
    return merge(cf_recs, cold_recs)
```

---

## 🌐 **API Endpoints**

### **Recommendation Endpoints**

| Endpoint                        | Method | Description                           | Parameters                                |
| ------------------------------- | ------ | ------------------------------------- | ----------------------------------------- |
| `/recommend/{user_id}`          | GET    | Main recommendation                   | `top_n=8`, `metadata=false`, `session_id` |
| `/recommend/sections/{user_id}` | GET    | Sectioned recommendations (Home page) | `limit_per_section=4`                     |
| `/recommend/vary/{user_id}`     | GET    | A/B testing variations                | `variation=balanced/explore`              |
| `/recommend/item/{product_id}`  | GET    | Similar products (Product detail)     | `top_n=4`                                 |
| `/recommend/simulate/{user_id}` | GET    | Simulation with metadata              | `top_n=4`                                 |

### **Admin Endpoints**

| Endpoint                     | Method | Description                     |
| ---------------------------- | ------ | ------------------------------- |
| `/admin/stats`               | GET    | Model performance statistics    |
| `/admin/cf-detail/{user_id}` | GET    | Step-by-step calculation detail |
| `/cache/refresh`             | POST   | Force refresh cache             |

### **Tracking Endpoints**

| Endpoint      | Method | Description        | Body                                |
| ------------- | ------ | ------------------ | ----------------------------------- |
| `/track/view` | POST   | Track product view | `{user_id, product_id, session_id}` |

### **Example Request/Response**

```bash
# Request
GET /recommend/119?top_n=8&metadata=true&session_id=abc123

# Response
{
    "user_id": 119,
    "recommendations": [
        {"id": 245, "origin": "BEST MATCH"},
        {"id": 243, "origin": "BEST MATCH"},
        {"id": 2, "origin": "FOR YOU"},
        {"id": 287, "origin": "STYLE MATCH"},
        {"id": 14, "origin": "HOT HITS"},
        {"id": 17, "origin": "TRENDING"},
        {"id": 139, "origin": "BEST SELLER"},
        {"id": 175, "origin": "NEW ARRIVAL"}
    ],
    "method": "pure_cf_v5_with_cold_start",
    "is_cold_start": false
}
```

---

## 🗄️ **Database Schema**

### **Required Tables**

```sql
-- Explicit Ratings
CREATE TABLE ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating DECIMAL(2,1) CHECK (rating >= 0.5 AND rating <= 5.0),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_product (product_id)
);

-- Orders & Order Items
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    status ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled'),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    qty INT DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Cart
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Wishlists
CREATE TABLE wishlists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Product Views (Implicit Signal)
CREATE TABLE product_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT NOT NULL,
    session_id VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_product (product_id),
    INDEX idx_session (session_id)
);

-- Products Master
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    category_id INT,
    price DECIMAL(10,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category_id)
);
```

### **Optional Tables**

```sql
-- New Products Boost (Cold Start)
CREATE TABLE new_products_boost (
    product_id INT PRIMARY KEY,
    boost_score DECIMAL(3,2) DEFAULT 1.5,
    expires_at DATETIME,
    INDEX idx_expires (expires_at)
);
```

---

## ⚙️ **Configuration & Tuning**

### **Hyperparameters**

| Parameter                | Default  | Description                                | Tuning Range |
| ------------------------ | -------- | ------------------------------------------ | ------------ |
| `alpha`                  | 0.6      | Bobot User-Based CF                        | 0.5 - 0.8    |
| `high_threshold_normal`  | 0.55     | Threshold untuk high confidence            | 0.4 - 0.7    |
| `high_threshold_explore` | 0.65     | Threshold untuk explore mode               | 0.5 - 0.8    |
| `n_neighbors_user`       | 16       | Jumlah tetangga untuk User-KNN             | 10 - 30      |
| `n_neighbors_item`       | 16       | Jumlah tetangga untuk Item-KNN             | 10 - 30      |
| `min_co_occurrence`      | 1        | Minimal co-occurrence untuk similarity     | 1 - 3        |
| `sparsity_threshold`     | 0.85     | Threshold untuk conditional mean-centering | 0.8 - 0.95   |
| `cache_ttl`              | 60 detik | Time-to-live cache                         | 30 - 300     |

### **Environment Variables**

```python
# Database Configuration (MAMP Standard)
DB_CONFIG = {
    "host": "127.0.0.1",
    "port": 8889,
    "user": "root",
    "password": "root",
    "database": "ecommerce_db",
    "charset": "utf8mb4"
}

# Model Configuration
MODEL_CONFIG = {
    "alpha": 0.6,
    "top_n_candidate": 100,
    "high_threshold_normal": 0.55,
    "high_threshold_explore": 0.65,
    "n_neighbors": 16
}
```

### **Tuning Guide**

| Scenario                            | Alpha | High Threshold | N Neighbors |
| ----------------------------------- | ----- | -------------- | ----------- |
| **High Accuracy**                   | 0.7   | 0.6            | 20          |
| **High Diversity**                  | 0.5   | 0.45           | 12          |
| **Cold Start (New User)**           | 0.4   | 0.5            | 25          |
| **Mature User (>100 interactions)** | 0.65  | 0.6            | 15          |

---

## 📈 **Performance Metrics**

### **Model Statistics (via /admin/stats)**

```json
{
	"density_score": 3.42, // Matrix density percentage
	"sim_strength": 45.2, // Average similarity (Top 5)
	"coverage_score": 78.5, // Users with ≥3 interactions
	"total_users": 1250,
	"total_items": 850,
	"total_signals": 15780,
	"source_distribution": {
		"purchase": 8500,
		"view": 4500,
		"cart": 1500,
		"wishlist": 800,
		"explicit": 480
	},
	"top_similarity_pairs": [
		{
			"p1": "Kaos Streetwear Cream",
			"p2": "Kaos Streetwear Maroon",
			"score": 0.85
		}
	],
	"final_confidence": 72.3
}
```

### **Confidence Formula**

```python
density_score = min(density, 50)
sim_score = min(sim_strength, 60)
coverage_score = coverage

if total_users < 50:
    confidence = 0.2*density_score + 0.3*sim_score + 0.5*coverage_score
elif total_users < 200:
    confidence = 0.25*density_score + 0.4*sim_score + 0.35*coverage_score
else:
    confidence = 0.3*density_score + 0.5*sim_score + 0.2*coverage_score
```

### **Performance Benchmarks**

| Users  | Items | Signals | Compute Time | Memory Usage |
| ------ | ----- | ------- | ------------ | ------------ |
| 100    | 50    | 500     | < 0.1 sec    | ~50 MB       |
| 1,000  | 500   | 5,000   | 0.5 sec      | ~200 MB      |
| 5,000  | 1,000 | 25,000  | 2 sec        | ~500 MB      |
| 10,000 | 5,000 | 100,000 | 5 sec        | ~2 GB        |

---

## 🐛 **Troubleshooting**

### **Common Issues & Solutions**

#### **1. Similarity 100% untuk semua produk**

**Penyebab:** Data terlalu sparse + mean-centering menghasilkan zero vectors

**Solusi:**

```python
# Sudah diimplementasikan di v5.3
if sparsity < 0.85:
    pivot_centered = pivot.sub(user_mean, axis=0).fillna(0)
else:
    pivot_centered = pivot.fillna(0)  # Skip mean-centering
```

#### **2. Produk dengan skor 0,0,0 muncul di dashboard**

**Penyebab:** Filter tidak memfilter skor 0

**Solusi:**

```python
# Implemented in v5.3
valid_pids = set()
for pid, score in u_norm.items():
    if score > 0: valid_pids.add(pid)
for pid, score in i_norm.items():
    if score > 0: valid_pids.add(pid)
```

#### **3. Recommended products not relevant**

**Penyebab:** Bobot alpha tidak sesuai

**Solusi:** Tuning alpha parameter

```python
# Jika user suka produk niche → naikkan alpha
alpha = 0.7

# Jika produk sering dibeli bersama → turunkan alpha
alpha = 0.5
```

#### **4. Slow response time**

**Penyebab:** Similarity recompute setiap request

**Solusi:**

```python
# TTL caching
self.ttl = 300  # 5 minutes instead of 60 seconds

# Atau gunakan Redis untuk cache
```

### **Debug Mode**

```python
# Aktifkan logging detail
logging.basicConfig(level=logging.DEBUG)

# Gunakan endpoint simulasi
GET /recommend/simulate/{user_id}

# Lihat step-by-step detail
GET /admin/cf-detail/{user_id}
```

---

## 📝 **Changelog**

### **v5.3 (2026-04-30)**

- ✅ Fixed: Filter skor 0 di dashboard Step 5
- ✅ Improved: Dynamic explore mode distribution
- ✅ Fixed: KNN user assignment logic
- ✅ Fixed: Remaining_candidates variable scope

### **v5.2 (2026-04-29)**

- ✅ Added: Global score normalization
- ✅ Added: Alpha weighting for hybrid scoring
- ✅ Added: Conditional mean-centering
- ✅ Fixed: Co-occurrence penalty logic

### **v5.1 (2026-04-28)**

- ✅ Added: KNN for User-Based CF
- ✅ Added: Cold start strategy
- ✅ Added: Multi-signal rating matrix

### **v5.0 (2026-04-27)**

- ✅ Initial release
- ✅ Pure Collaborative Filtering
- ✅ User-Based + Item-Based CF

---

## 🤝 **Support & Maintenance**

### **Monitoring Checklist**

- [ ] Response time < 500ms
- [ ] Cache hit ratio > 80%
- [ ] Coverage score > 50%
- [ ] Daily active users tracking
- [ ] Click-through rate (CTR) monitoring

### **Regular Maintenance**

| Task                    | Frequency      | Command                                                                |
| ----------------------- | -------------- | ---------------------------------------------------------------------- |
| Refresh cache           | Every 60 sec   | Auto (based on TTL)                                                    |
| Clean old product_views | Daily          | `DELETE FROM product_views WHERE created_at < NOW() - INTERVAL 90 DAY` |
| Update similar products | Every 5 min    | Auto (cache TTL)                                                       |
| Retrain model           | On data change | Auto (signature detection)                                             |

### **Contact**

- **Engine Version:** 5.3
- **Last Updated:** 2026-04-30
- **Maintainer:** JiDoor Team

---

## 📚 **References**

1. **Paper:** "Item-Based Collaborative Filtering Recommendation Algorithms" - Sarwar et al.
2. **Library:** Scikit-learn Cosine Similarity Documentation
3. **Best Practice:** "Matrix Factorization Techniques for Recommender Systems" - Koren, Bell, Volinsky

---

**© 2026 JiDoor - Pure Collaborative Filtering Engine v5.3**
