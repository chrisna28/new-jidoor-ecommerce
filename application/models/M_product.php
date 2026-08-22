 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model: M_product
 * Mengelola data produk
 */
class M_product extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Ambil semua produk dengan join kategori
     */
    public function get_all($limit = null, $offset = 0, $recommended_ids = []) {
        $this->db->select('p.*, c.name as category_name, c.slug as category_slug, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count, COALESCE(oi_sub.total_sold, 0) as total_sold');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('ratings r', 'r.product_id = p.id', 'left');
        $this->db->join('(SELECT product_id, SUM(qty) as total_sold FROM order_items GROUP BY product_id) oi_sub', 'oi_sub.product_id = p.id', 'left');
        $this->db->group_by('p.id');
        
        if (!empty($recommended_ids)) {
            $ids_str = implode(',', array_map('intval', $recommended_ids));
            $this->db->order_by("p.id IN ($ids_str)", 'DESC', FALSE);
            $this->db->order_by("FIELD(p.id, $ids_str)", 'ASC', FALSE);
        }
        
        $this->db->order_by('p.id', 'DESC');
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    /**
     * Hitung total produk
     */
    public function count_all() {
        return $this->db->count_all('products');
    }

    /**
     * Daftar ringkas produk untuk picker rekomendasi di chat admin
     */
    public function get_chat_list() {
        return $this->db->select('id, name, price, image')
                        ->from('products')
                        ->order_by('name', 'ASC')
                        ->get()->result_array();
    }

    /**
     * Hitung produk per kategori
     */
    public function count_by_category($category_slug) {
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('c.slug', $category_slug);
        return $this->db->count_all_results();
    }

    /**
     * Ambil produk berdasarkan ID
     */
    public function get_by_id($id) {
        $this->db->select('p.*, c.name as category_name, c.slug as category_slug, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count, COALESCE(oi_sub.total_sold, 0) as total_sold');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('ratings r', 'r.product_id = p.id', 'left');
        $this->db->join('(SELECT product_id, SUM(qty) as total_sold FROM order_items GROUP BY product_id) oi_sub', 'oi_sub.product_id = p.id', 'left');
        $this->db->where('p.id', $id);
        $this->db->group_by('p.id');
        return $this->db->get()->row();
    }

    /**
     * Ambil produk berdasarkan slug
     */
    public function get_by_slug($slug) {
        $this->db->select('p.*, c.name as category_name, c.slug as category_slug, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count, COALESCE(oi_sub.total_sold, 0) as total_sold');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('ratings r', 'r.product_id = p.id', 'left');
        $this->db->join('(SELECT product_id, SUM(qty) as total_sold FROM order_items GROUP BY product_id) oi_sub', 'oi_sub.product_id = p.id', 'left');
        $this->db->where('p.slug', $slug);
        $this->db->group_by('p.id');
        return $this->db->get()->row();
    }

    /**
     * Ambil produk berdasarkan kategori
     */
    public function get_by_category($category_slug, $limit = null, $offset = 0, $recommended_ids = []) {
        $this->db->select('p.*, c.name as category_name, c.slug as category_slug, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count, COALESCE(oi_sub.total_sold, 0) as total_sold');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('ratings r', 'r.product_id = p.id', 'left');
        $this->db->join('(SELECT product_id, SUM(qty) as total_sold FROM order_items GROUP BY product_id) oi_sub', 'oi_sub.product_id = p.id', 'left');
        $this->db->where('c.slug', $category_slug);
        $this->db->group_by('p.id');
        
        if (!empty($recommended_ids)) {
            $ids_str = implode(',', array_map('intval', $recommended_ids));
            $this->db->order_by("p.id IN ($ids_str)", 'DESC', FALSE);
            $this->db->order_by("FIELD(p.id, $ids_str)", 'ASC', FALSE);
        }
        
        $this->db->order_by('p.id', 'DESC');
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    /**
     * Ambil produk berdasarkan array ID (untuk rekomendasi)
     */
    public function get_by_ids($ids) {
        if (empty($ids)) return [];
        $this->db->select('p.*, c.name as category_name, c.slug as category_slug, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count, COALESCE(oi_sub.total_sold, 0) as total_sold');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('ratings r', 'r.product_id = p.id', 'left');
        $this->db->join('(SELECT product_id, SUM(qty) as total_sold FROM order_items GROUP BY product_id) oi_sub', 'oi_sub.product_id = p.id', 'left');
        $this->db->where_in('p.id', $ids);
        $this->db->group_by('p.id');
        return $this->db->get()->result();
    }

    /**
     * Ambil produk terbaru (fallback rekomendasi)
     */
    public function get_latest($limit = 8) {
        $this->db->select('p.*, c.name as category_name, c.slug as category_slug, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count, COALESCE(oi_sub.total_sold, 0) as total_sold');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('ratings r', 'r.product_id = p.id', 'left');
        $this->db->join('(SELECT product_id, SUM(qty) as total_sold FROM order_items GROUP BY product_id) oi_sub', 'oi_sub.product_id = p.id', 'left');
        $this->db->group_by('p.id');
        $this->db->order_by('p.id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /**
     * Pencarian produk
     */
    public function search($keyword, $limit = null, $offset = 0, $recommended_ids = []) {
        $words = explode(' ', $keyword);
        $words = array_filter(array_map('trim', $words));
        
        // Calculate unified relevance score
        $relevance = "(CASE WHEN p.name LIKE '%" . $this->db->escape_like_str($keyword) . "%' THEN 100 ELSE 0 END) + ";
        $relevance .= "(CASE WHEN c.name LIKE '%" . $this->db->escape_like_str($keyword) . "%' THEN 50 ELSE 0 END) + ";
        if (count($words) > 1) {
            foreach ($words as $word) {
                if (strlen($word) > 2) {
                    $esc_word = $this->db->escape_like_str($word);
                    $relevance .= "(CASE WHEN p.name LIKE '%$esc_word%' THEN 10 ELSE 0 END) + ";
                    $relevance .= "(CASE WHEN c.name LIKE '%$esc_word%' THEN 5 ELSE 0 END) + ";
                }
            }
        }
        $relevance .= "0";
        
        $this->db->select("p.*, c.name as category_name, c.slug as category_slug, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count, COALESCE(oi_sub.total_sold, 0) as total_sold, ($relevance) as relevance_score", FALSE);
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('ratings r', 'r.product_id = p.id', 'left');
        $this->db->join('(SELECT product_id, SUM(qty) as total_sold FROM order_items GROUP BY product_id) oi_sub', 'oi_sub.product_id = p.id', 'left');
        
        $this->db->group_start();
        // Exact Phrase Match
        $this->db->like('p.name', $keyword);
        $this->db->or_like('p.description', $keyword);
        $this->db->or_like('c.name', $keyword);
        
        // Individual Word Match
        if (count($words) > 1) {
            foreach ($words as $word) {
                if (strlen($word) > 2) { // Ignore short common words like 'di', 'ke'
                    $this->db->or_like('p.name', $word);
                    $this->db->or_like('p.description', $word);
                    $this->db->or_like('c.name', $word);
                }
            }
        }
        $this->db->group_end();
        $this->db->group_by('p.id');
        
        // 1. Keyword Relevance Ordering (Absolute Priority)
        $this->db->order_by('relevance_score', 'DESC');
        
        // 2. AI Recommendation Ordering (As Tie-Breaker)
        if (!empty($recommended_ids)) {
            $ids_str = implode(',', array_map('intval', $recommended_ids));
            $this->db->order_by("p.id IN ($ids_str)", 'DESC', FALSE);
            $this->db->order_by("FIELD(p.id, $ids_str)", 'ASC', FALSE);
        }

        // 3. Fallback Ordering
        $this->db->order_by('p.id', 'DESC');
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    /**
     * Hitung hasil pencarian
     */
    public function count_search($keyword) {
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        
        $words = explode(' ', $keyword);
        $words = array_filter(array_map('trim', $words));
        
        $this->db->group_start();
        $this->db->like('p.name', $keyword);
        $this->db->or_like('p.description', $keyword);
        $this->db->or_like('c.name', $keyword);
        
        if (count($words) > 1) {
            foreach ($words as $word) {
                if (strlen($word) > 2) {
                    $this->db->or_like('p.name', $word);
                    $this->db->or_like('p.description', $word);
                    $this->db->or_like('c.name', $word);
                }
            }
        }
        $this->db->group_end();
        
        return $this->db->count_all_results();
    }

    /**
     * Insert produk baru
     */
    public function insert($data) {
        $this->db->insert('products', $data);
        return $this->db->insert_id();
    }

    /**
     * Update produk
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('products', $data);
    }

    /**
     * Hapus produk
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('products');
    }

    /**
     * Ambil semua kategori
     */
    public function get_categories() {
        return $this->db->order_by('name', 'ASC')->get('categories')->result();
    }

    /**
     * Ambil distribusi produk per kategori (untuk chart)
     */
    public function get_category_distribution() {
        $this->db->select('c.name as category_label, COUNT(p.id) as product_count');
        $this->db->from('categories c');
        $this->db->join('products p', 'p.category_id = c.id', 'left');
        $this->db->group_by('c.id');
        return $this->db->get()->result();
    }

    /**
     * Ambil kategori berdasarkan ID
     */
    public function get_category_by_id($id) {
        return $this->db->get_where('categories', ['id' => $id])->row();
    }

    /**
     * Tambah kategori baru
     */
    public function insert_category($data) {
        return $this->db->insert('categories', $data);
    }

    /**
     * Update kategori
     */
    public function update_category($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('categories', $data);
    }

    /**
     * Hapus kategori
     */
    public function delete_category($id) {
        $this->db->where('id', $id);
        return $this->db->delete('categories');
    }

    /**
     * Kurangi stok produk
     */
    public function reduce_stock($product_id, $qty) {
        $this->db->set('stock', 'stock - ' . (int)$qty, FALSE);
        $this->db->where('id', $product_id);
        return $this->db->update('products');
    }

    /**
     * Ambil produk dengan stok rendah
     */
    public function get_low_stock($threshold = 5) {
        $this->db->select('p.id, p.name, p.stock, p.image, c.name as category_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.stock <=', $threshold);
        $this->db->order_by('p.stock', 'ASC');
        $this->db->limit(10);
        return $this->db->get()->result();
    }

    // =======================================================
    // VARIAN PRODUK (Revisi #2)
    // =======================================================

    /**
     * Ambil semua varian suatu produk
     */
    public function get_variants($product_id) {
        return $this->db->where('product_id', $product_id)
                        ->order_by('id', 'ASC')
                        ->get('product_variants')->result();
    }

    /**
     * Ambil satu varian berdasarkan ID
     */
    public function get_variant($variant_id) {
        return $this->db->get_where('product_variants', ['id' => $variant_id])->row();
    }

    /**
     * Simpan ulang seluruh varian produk (strategi replace-all).
     * Input dari array form repeater admin.
     */
    public function save_variants($product_id, $colors, $sizes, $stocks, $price_deltas) {
        $rows = [];
        $n = count($colors);
        for ($i = 0; $i < $n; $i++) {
            $color = trim($colors[$i]);
            $size  = trim($sizes[$i]);
            if ($color === '' && $size === '') {
                continue; // lewati baris kosong
            }
            $rows[] = [
                'product_id'  => $product_id,
                'color'       => $color !== '' ? $color : 'Standar',
                'size'        => $size !== '' ? $size : 'Standar',
                'stock'       => max(0, (int)$stocks[$i]),
                'price_delta' => max(0, (float)$price_deltas[$i]),
            ];
        }
        if (empty($rows)) {
            // Minimal satu varian standar agar produk tetap bisa dibeli
            $rows[] = [
                'product_id'  => $product_id,
                'color'       => 'Standar',
                'size'        => 'Standar',
                'stock'       => 0,
                'price_delta' => 0,
            ];
        }

        $this->db->trans_start();
        $this->db->where('product_id', $product_id)->delete('product_variants');
        $this->db->insert_batch('product_variants', $rows);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Kurangi stok varian
     */
    public function reduce_variant_stock($variant_id, $qty) {
        $this->db->set('stock', 'stock - ' . (int)$qty, FALSE);
        $this->db->where('id', $variant_id);
        return $this->db->update('product_variants');
    }

    // =======================================================
    // LIKE PRODUK (Revisi #1)
    // =======================================================

    /**
     * Toggle like produk oleh user (unik per user per produk)
     */
    public function toggle_like($user_id, $product_id) {
        $exists = $this->db->get_where('likes', [
            'user_id'    => $user_id,
            'product_id' => $product_id
        ])->row();

        if ($exists) {
            $this->db->delete('likes', ['id' => $exists->id]);
            $liked = FALSE;
        } else {
            $this->db->insert('likes', [
                'user_id'    => $user_id,
                'product_id' => $product_id
            ]);
            $liked = TRUE;
        }

        return [
            'liked'      => $liked,
            'like_count' => $this->count_likes($product_id)
        ];
    }

    /**
     * Jumlah like suatu produk
     */
    public function count_likes($product_id) {
        return $this->db->where('product_id', $product_id)
                        ->count_all_results('likes');
    }

    /**
     * Cek apakah user sudah like produk
     */
    public function is_liked_by($user_id, $product_id) {
        return $this->db->get_where('likes', [
            'user_id'    => $user_id,
            'product_id' => $product_id
        ])->num_rows() > 0;
    }

    /**
     * Tempelkan like_count & comment_count ke array objek produk.
     * Dipakai kartu produk di home/katalog/search/detail terkait.
     * Jika $user_id diberikan, juga set is_liked per produk.
     */
    public function attach_social_counts($products, $user_id = null) {
        if (empty($products)) return $products;

        $ids = [];
        foreach ($products as $p) {
            $ids[] = (int)$p->id;
        }

        $like_map = $comment_map = [];
        $liked_ids = [];

        $likes = $this->db->select('product_id, COUNT(*) AS total')
                          ->where_in('product_id', $ids)
                          ->group_by('product_id')
                          ->get('likes')->result();
        foreach ($likes as $l) {
            $like_map[$l->product_id] = (int)$l->total;
        }

        $comments = $this->db->select('product_id, COUNT(*) AS total')
                             ->where_in('product_id', $ids)
                             ->where("review IS NOT NULL AND review <> ''", NULL, FALSE)
                             ->group_by('product_id')
                             ->get('ratings')->result();
        foreach ($comments as $c) {
            $comment_map[$c->product_id] = (int)$c->total;
        }

        if ($user_id) {
            $my_likes = $this->db->select('product_id')
                                 ->where('user_id', $user_id)
                                 ->where_in('product_id', $ids)
                                 ->get('likes')->result();
            foreach ($my_likes as $ml) {
                $liked_ids[] = (int)$ml->product_id;
            }
        }

        foreach ($products as $p) {
            $p->like_count    = isset($like_map[$p->id]) ? $like_map[$p->id] : 0;
            $p->comment_count = isset($comment_map[$p->id]) ? $comment_map[$p->id] : 0;
            $p->is_liked      = in_array((int)$p->id, $liked_ids);
        }
        return $products;
    }
}
