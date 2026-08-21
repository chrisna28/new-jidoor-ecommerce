import pymysql
import re
from typing import List, Dict, Any, Optional

"""
JI-DOOR SEARCH INTELLIGENCE MODULE
Module ini mengelola seluruh logika yang berhubungan dengan pencarian produk:
1. Log Tracking: Mencatat setiap query yang diketik user.
2. Search Analytics: Mengumpulkan tren kata kunci populer untuk admin.
3. Reranking: Memberikan saran produk berdasarkan kecocokan keyword.
"""

class SearchIntelligence:
    """
    Kelas untuk mengelola interaksi pencarian dan analitik niat beli pengguna.
    """
    def __init__(self, db_config: Dict[str, Any] = None):
        # Gunakan DB_CONFIG default jika tidak disediakan (MAMP Port 8889)
        self.db_config = db_config or {
            "host": "127.0.0.1",
            "port": 8889,
            "user": "root",
            "password": "root",
            "database": "ecommerce_db",
            "cursorclass": pymysql.cursors.DictCursor
        }
        self.stopwords = {"dan", "yang", "untuk", "dengan", "pintu", "di", "ke", "dari"}

    def get_db_connection(self):
        return pymysql.connect(**self.db_config)

    def extract_intent(self, query: str) -> Dict[str, Any]:
        """Ekstraksi intent dengan fuzzy matching"""
        query = query.lower()
        
        # Category mapping
        category_keywords = {
            "baju": ["baju", "kaos", "kemeja", "t-shirt", "shirt"],
            "jaket": ["jaket", "hoodie", "jacket", "sweater"],
            "aksesoris": ["lanyard", "topi", "cap", "pin", "sticker"]
        }
        
        # Material mapping  
        material_keywords = {
            "kayu": ["kayu", "wood", "jati"],
            "besi": ["besi", "metal", "stainless"],
            "kain": ["kain", "cotton", "polyester", "katun"]
        }
        
        intent = {
            "category": None,
            "material": None,
            "keywords": []
        }
        
        # Detect category
        for cat, keywords in category_keywords.items():
            if any(kw in query for kw in keywords):
                intent["category"] = cat
                break
        
        # Detect material
        for mat, keywords in material_keywords.items():
            if any(kw in query for kw in keywords):
                intent["material"] = mat
                break
        
        # Extract all meaningful words
        words = re.findall(r'[\w\-]+', query)
        intent["keywords"] = [w for w in words if w not in self.stopwords and len(w) > 2]
        
        return intent

    def log_search(self, data: Dict[str, Any]):
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                sql = """INSERT INTO search_logs 
                         (user_id, search_query, results_count, clicked_product_id, clicked_position, session_id) 
                         VALUES (%s, %s, %s, %s, %s, %s)"""
                cursor.execute(sql, (
                    data.get('user_id'), data.get('query'), data.get('results_count', 0),
                    data.get('clicked_product_id'), data.get('clicked_position'), data.get('session_id')
                ))
                conn.commit()
        finally: conn.close()

    def get_search_based_recs(self, query: str, limit: int = 8) -> List[int]:
        """
        Cari produk yang paling relevan dengan keyword pencarian.
        Menggunakan pembobotan skor (3x pada nama, 1x pada deskripsi).
        """
        intent = self.extract_intent(query)
        keywords = intent["keywords"]
        if not keywords: return []
        
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                # Weighted Search Query
                sql = """
                    SELECT id, (
                        (CASE WHEN name LIKE %s THEN 3 ELSE 0 END) +
                        (CASE WHEN description LIKE %s THEN 1 ELSE 0 END)
                    ) as score
                    FROM products
                    WHERE name LIKE %s OR description LIKE %s
                    ORDER BY score DESC LIMIT %s
                """
                like_query = f"%{keywords[0]}%"
                cursor.execute(sql, (like_query, like_query, like_query, like_query, limit))
                return [r['id'] for r in cursor.fetchall()]
        finally: conn.close()

    def get_search_analytics(self, days: int = 30) -> Dict[str, Any]:
        conn = self.get_db_connection()
        try:
            with conn.cursor() as cursor:
                # Top Queries
                cursor.execute("""
                    SELECT search_query, COUNT(*) as count, COUNT(DISTINCT user_id) as users,
                           (SUM(CASE WHEN clicked_product_id IS NOT NULL THEN 1 ELSE 0 END) / COUNT(*)) * 100 as ctr
                    FROM search_logs 
                    WHERE search_date >= DATE_SUB(NOW(), INTERVAL %s DAY)
                    GROUP BY search_query ORDER BY count DESC LIMIT 20
                """, (days,))
                top_queries = cursor.fetchall()

                # Zero Results (Opportunities)
                cursor.execute("""
                    SELECT search_query, COUNT(*) as count 
                    FROM search_logs WHERE results_count = 0 
                    GROUP BY search_query ORDER BY count DESC LIMIT 10
                """)
                zero_results = cursor.fetchall()
                
                # Map results to expected format
                formatted_keywords = []
                for q in top_queries:
                    formatted_keywords.append({
                        "keyword": q['search_query'],
                        "count": q['count'],
                        "users": q['users'],
                        "ctr": q['ctr']
                    })
                
                return {
                    "top_keywords": formatted_keywords,
                    "zero_results": zero_results,
                    "total_searches": sum(q['count'] for q in top_queries)
                }
        finally: conn.close()
