<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller: Welcome (Frontend)
 * Halaman publik: Home, Katalog, Detail Produk, Search, Rating
 */
class Welcome extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model(['M_product', 'M_rating', 'M_cart', 'M_order', 'M_wishlist']);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------
    private function _cart_count() {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) return 0;
        return $this->M_cart->count_cart($user_id);
    }

    private function _wishlist_count() {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) return 0;
        return $this->M_wishlist->count_wishlist($user_id);
    }

    // -------------------------------------------------------
    // Helper: ambil jumlah pesanan aktif (paid/shipped) untuk notif
    // -------------------------------------------------------
    private function _notification_count() {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) return 0;
        return $this->M_order->count_user_active_orders($user_id);
    }

    /**
     * Halaman Riwayat Rating User
     */
    public function rating_history() {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            $this->session->set_flashdata('error', 'Silakan login untuk melihat riwayat rating.');
            redirect('login');
        }

        $data = [
            'title'      => 'Riwayat Rating Saya',
            'ratings'    => $this->M_rating->get_user_ratings_with_product($user_id),
            'cart_count' => $this->_cart_count(),
            'wishlist_count' => $this->_wishlist_count(),
            'notif_count'=> $this->_notification_count(),
            'categories' => $this->M_product->get_categories()
        ];

        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_rating_history', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    // -------------------------------------------------------
    // Helper: panggil Python API via cURL
    // -------------------------------------------------------
    private function _get_recommendations($user_id, $limit = 4, $type = 'hybrid', $with_metadata = false) {
        $api_url = 'http://127.0.0.1:8000/recommend/' . (int)$user_id . '?top_n=' . (int)$limit;
        if ($with_metadata) $api_url .= '&metadata=true';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) return [];
        $result = json_decode($response, TRUE);

        if ($with_metadata) {
            return isset($result['recommendations']) ? $result['recommendations'] : [];
        }
        return isset($result['recommended_product_ids']) ? $result['recommended_product_ids'] : [];
    }

    private function _get_sectioned_recommendations($user_id, $limit_per_section = 8) {
        $api_url = 'http://127.0.0.1:8000/recommend/sections/' . (int)$user_id . '?limit_per_section=' . (int)$limit_per_section;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) return [];
        $result = json_decode($response, TRUE);
        return isset($result['sections']) ? $result['sections'] : [];
    }

    // -------------------------------------------------------
    // HOME — menampilkan rekomendasi dan produk terbaru
    // -------------------------------------------------------
    public function index() {
        $user_id         = $this->session->userdata('user_id');
        $sections        = [];
        
        $categories = $this->M_product->get_categories();
        $latest_products = [];
        
        // Ambil 4 produk terbaru dari setiap kategori untuk All Collection
        foreach ($categories as $cat) {
            $this->db->order_by('created_at', 'DESC');
            $cat_products = $this->M_product->get_by_category($cat->slug, 4); 
            if (!empty($cat_products)) {
                $latest_products = array_merge($latest_products, $cat_products);
            }
        }

        // Ambil rekomendasi per section
        $raw_sections = $this->_get_sectioned_recommendations($user_id ? $user_id : 0, 4);
        foreach ($raw_sections as $sec) {
            $ids = array_column($sec['items'], 'id');
            $products = $this->M_product->get_by_ids($ids);
            
            // Map details back to items while maintaining order
            $sec_products = [];
            foreach ($ids as $id) {
                foreach ($products as $p) {
                    if ($p->id == $id) {
                        // Temukan origin untuk badge
                        foreach ($sec['items'] as $item) {
                            if ($item['id'] == $id) {
                                $p->badge_text = $item['origin'];
                                break;
                            }
                        }
                        $sec_products[] = $p;
                        break;
                    }
                }
            }
            
            if (!empty($sec_products)) {
                $sec_products = $this->M_product->attach_social_counts($sec_products, $user_id);
                $sections[] = [
                    'title' => $sec['title'],
                    'origin' => $sec['origin'],
                    'products' => $sec_products
                ];
            }
        }

        $latest_products = $this->M_product->attach_social_counts($latest_products, $user_id);

        $data = [
            'title'           => 'JiDoor Store — Toko Pintu & Aksesoris Terbaik',
            'sections'        => $sections,
            'latest_products' => $latest_products,
            'categories'      => $categories,
            'user_wishlist_ids' => $this->_get_user_wishlist_ids(),
            'cart_count'      => $this->_cart_count(),
            'wishlist_count'  => $this->_wishlist_count(),
            'notif_count'     => $this->_notification_count(),
            'total_orders'    => $this->M_order->count_all(),
        ];

        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_home', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    // -------------------------------------------------------
    // KATALOG — semua produk dengan pagination
    // -------------------------------------------------------
    public function katalog($offset = 0) {
        $per_page = 12;

        $this->load->library('pagination');
        $config['base_url']   = base_url('katalog/');
        $config['total_rows'] = $this->M_product->count_all();
        $config['per_page']   = $per_page;
        $config['full_tag_open']  = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['num_tag_open']   = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close']  = '</span></li>';
        $config['cur_tag_open']   = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close']  = '</span></li>';
        $config['prev_link']      = '&laquo;';
        $config['next_link']      = '&raquo;';
        $config['prev_tag_open']  = '<li class="page-item"><span class="page-link">';
        $config['prev_tag_close'] = '</span></li>';
        $config['next_tag_open']  = '<li class="page-item"><span class="page-link">';
        $config['next_tag_close'] = '</span></li>';
        $this->pagination->initialize($config);

        $from = $this->uri->segment(2);
        if ($from === null || $from === '') $from = 0;

        $user_id         = $this->session->userdata('user_id');
        $recommended_ids = [];
        $origins         = [];

        // Ambil rekomendasi dari API sections agar semua origin (termasuk Discovery & Fresh) terbaca
        $api_recs = $this->_get_recommendations($user_id ? $user_id : 0, 1000, 'hybrid', true);
        foreach ($api_recs as $item) {
            if (!isset($origins[$item['id']])) {
                $origins[$item['id']] = $item['origin'];
                $recommended_ids[] = $item['id'];
            }
        }

        $data_products = $this->M_product->get_all($per_page, $from, $recommended_ids);
        $data_products = $this->M_product->attach_social_counts($data_products, $user_id);

        // -----------------------------------------------------------
        // DIVERSITY BADGES — Assign contextual badges to products
        // that don't have an AI-assigned origin, based on real data
        // -----------------------------------------------------------
        $origins = $this->_diversify_badges($data_products, $origins);
        // Update recommended_ids to include all products with badges
        $recommended_ids = array_keys($origins);

        $data = [
            'title'           => 'Katalog Produk — JiDoor Store',
            'products'        => $data_products,
            'recommended_ids' => $recommended_ids,
            'rec_origins'     => $origins,
            'categories'      => $this->M_product->get_categories(),
            'user_wishlist_ids' => $this->_get_user_wishlist_ids(),
            'cart_count'      => $this->_cart_count(),
            'wishlist_count'  => $this->_wishlist_count(),
            'notif_count'     => $this->_notification_count(),
            'pagination'      => $this->pagination->create_links(),
        ];

        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_katalog', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    // -------------------------------------------------------
    // FILTER BY KATEGORI
    // -------------------------------------------------------
    public function by_category($slug, $offset = 0) {
        $per_page = 12;

        $this->load->library('pagination');
        $config['base_url']   = base_url('kategori/' . $slug . '/');
        $config['total_rows'] = $this->M_product->count_by_category($slug);
        $config['per_page']   = $per_page;
        $config['full_tag_open']  = '<nav><ul class="pagination justify-content-center">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['num_tag_open']   = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close']  = '</span></li>';
        $config['cur_tag_open']   = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close']  = '</span></li>';
        $this->pagination->initialize($config);

        $from = $this->uri->segment(3);
        if ($from === '') $from = 0;

        $user_id         = $this->session->userdata('user_id');
        $recommended_ids = [];
        $origins         = [];
        // Ambil rekomendasi dari API dengan limit tinggi (Uncapped seperti Step 6) agar semua origin terbaca
        $api_recs = $this->_get_recommendations($user_id ? $user_id : 0, 1000, 'hybrid', true);
        foreach ($api_recs as $item) {
            if (!isset($origins[$item['id']])) {
                $origins[$item['id']] = $item['origin'];
                $recommended_ids[] = $item['id'];
            }
        }

        $data_products = $this->M_product->get_by_category($slug, $per_page, $from, $recommended_ids);
        $data_products = $this->M_product->attach_social_counts($data_products, $user_id);

        // Diversity badges
        $origins = $this->_diversify_badges($data_products, $origins);
        $recommended_ids = array_keys($origins);

        $data = [
            'title'           => 'Kategori: ' . ucwords(str_replace('-', ' ', $slug)) . ' — JiDoor',
            'products'        => $data_products,
            'recommended_ids' => $recommended_ids,
            'rec_origins'     => $origins,
            'categories'      => $this->M_product->get_categories(),
            'user_wishlist_ids' => $this->_get_user_wishlist_ids(),
            'cart_count'      => $this->_cart_count(),
            'wishlist_count'  => $this->_wishlist_count(),
            'notif_count'     => $this->_notification_count(),
            'active_slug'     => $slug,
            'pagination'      => $this->pagination->create_links(),
        ];

        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_katalog', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    // -------------------------------------------------------
    // DETAIL PRODUK
    // -------------------------------------------------------
    public function detail($slug) {
        $product = $this->M_product->get_by_slug($slug);
        if (!$product) {
            redirect('katalog');
        }

        $user_id     = $this->session->userdata('user_id');
        $user_rating = null;
        $avg_rating  = $this->M_rating->get_avg_rating($product->id);

        if ($user_id) {
            $user_rating = $this->M_rating->check_rated($user_id, $product->id);
        }

        // Dapatkan rekomendasi AI (Item-Based)
        $similar_ids = $this->_get_similar_items($product->id);
        $related = [];

        if (!empty($similar_ids)) {
            foreach ($similar_ids as $sid) {
                $p = $this->M_product->get_by_id($sid);
                if ($p) $related[] = $p;
            }
        }

        // Fallback ke kategori sama jika AI tidak mengembalikan cukup item (kurang dari 4)
        if (count($related) < 4) {
            $cat_related = $this->M_product->get_by_category($product->category_slug, 8, 0);
            foreach ($cat_related as $cp) {
                if ($cp->id != $product->id && !in_array($cp, $related)) {
                    $related[] = $cp;
                }
                if (count($related) >= 4) break;
            }
        }

        // Dapatkan rekomendasi hybrid global (untuk badges tambahan seperti Trending/Personalized)
        $hybrid_recs = $this->_get_recommendations($user_id ? $user_id : 0, 100, 'hybrid', true);
        $hybrid_ids = array_column($hybrid_recs, 'id');
        $hybrid_origins = [];
        foreach ($hybrid_recs as $hr) { $hybrid_origins[$hr['id']] = $hr['origin']; }

        // Count like & komentar (Revisi #1)
        $related = $this->M_product->attach_social_counts($related, $user_id);

        $data = [
            'title'       => $product->name . ' — JiDoor Store',
            'product'     => $product,
            'variants'    => $this->M_product->get_variants($product->id),
            'avg_rating'  => $avg_rating,
            'user_rating' => $user_rating,
            'is_wishlist' => $user_id ? $this->M_wishlist->check_exists($user_id, $product->id) : false,
            'like_count'    => $this->M_product->count_likes($product->id),
            'comment_count' => $this->M_rating->count_reviews($product->id),
            'is_liked'      => $user_id ? $this->M_product->is_liked_by($user_id, $product->id) : false,
            'related'     => array_values($related),
            'similar_ids' => $similar_ids,
            'recommended_ids' => $hybrid_ids,
            'rec_origins'     => $hybrid_origins,
            'reviews'     => $this->M_rating->get_product_reviews($product->id),
            'categories'  => $this->M_product->get_categories(),
            'cart_count'  => $this->_cart_count(),
            'wishlist_count' => $this->_wishlist_count(),
            'notif_count' => $this->_notification_count(),
        ];

        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_detail', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    // -------------------------------------------------------
    // SEARCH PRODUK
    // -------------------------------------------------------
    public function search() {
        // Post-Redirect-Get pattern for clean URLs
        $post_keyword = $this->input->post('q', TRUE);
        if ($post_keyword !== NULL) {
            redirect('search/' . urlencode(trim($post_keyword)));
        }

        $keyword = trim(urldecode($this->uri->segment(2)));
        $per_page = 12;

        $this->load->library('pagination');
        $config['base_url']   = base_url('search/' . urlencode($keyword) . '/');
        $config['total_rows'] = $this->M_product->count_search($keyword);
        $config['per_page']   = $per_page;
        $config['full_tag_open']  = '<nav><ul class="pagination justify-content-center">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['num_tag_open']   = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close']  = '</span></li>';
        $config['cur_tag_open']   = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close']  = '</span></li>';
        $this->pagination->initialize($config);

        $from = $this->uri->segment(3);
        if ($from === '') $from = 0;

        $user_id = $this->session->userdata('user_id');
        $recommended_ids = [];
        $origins = [];
        
        $recs = $this->_get_recommendations($user_id ? $user_id : 0, 100, 'hybrid', true);
        if (!empty($recs)) {
            $recommended_ids = array_column($recs, 'id');
            foreach ($recs as $r) {
                $origins[$r['id']] = $r['origin'];
            }
        }

        $data = [
            'title'      => 'Hasil Pencarian: "' . htmlspecialchars($keyword) . '" — JiDoor',
            'keyword'    => $keyword,
            'products'   => $this->M_product->attach_social_counts(
                                $this->M_product->search($keyword, $per_page, $from, $recommended_ids),
                                $user_id
                            ),
            'recommended_ids' => $recommended_ids,
            'rec_origins' => $origins,
            'categories' => $this->M_product->get_categories(),
            'user_wishlist_ids' => $this->_get_user_wishlist_ids(),
            'cart_count' => $this->_cart_count(),
            'wishlist_count' => $this->_wishlist_count(),
            'notif_count'=> $this->_notification_count(),
            'pagination' => $this->pagination->create_links(),
        ];

        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_katalog', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    // -------------------------------------------------------
    // SIMPAN RATING PRODUK
    // -------------------------------------------------------
    public function rate() {
        if (!$this->session->userdata('user_id')) {
            redirect('login');
        }
        $user_id    = $this->session->userdata('user_id');
        $product_id = (int)$this->input->post('product_id');
        $rating     = (int)$this->input->post('rating');
        $review     = $this->input->post('review', TRUE);

        if ($product_id && $rating >= 1 && $rating <= 5) {
            $this->M_rating->add_or_update($user_id, $product_id, $rating, $review);
            $this->session->set_flashdata('success', 'Rating berhasil disimpan. Terima kasih!');
        } else {
            $this->session->set_flashdata('error', 'Rating tidak valid.');
        }

        $product = $this->M_product->get_by_id($product_id);
        redirect($product ? 'produk/' . $product->slug : 'katalog');
    }

    // -------------------------------------------------------
    // 404 NOT FOUND
    // -------------------------------------------------------
    public function notfound() {
        $data = [
            'title'      => '404 — Halaman Tidak Ditemukan',
            'cart_count' => $this->_cart_count(),
            'wishlist_count' => $this->_wishlist_count(),
            'categories' => $this->M_product->get_categories(),
        ];
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_notfound', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    /**
     * Call Python API for similar items (Item-Based CF)
     */
    private function _get_similar_items($product_id) {
        $url = "http://127.0.0.1:8000/recommend/item/" . $product_id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $data = json_decode($response, true);
            if (isset($data['similar_product_ids'])) {
                return $data['similar_product_ids'];
            }
        }
        return [];
    }

    // -------------------------------------------------------
    // SUBMIT RATING & REVIEW

    // -------------------------------------------------------
    public function wishlist() {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            $this->session->set_flashdata('error', 'Silakan login untuk melihat wishlist.');
            redirect('login');
        }

        $data = [
            'title'          => 'Wishlist Saya — JiDoor',
            'wishlist'       => $this->M_wishlist->get_user_wishlist($user_id),
            'cart_count'     => $this->_cart_count(),
            'wishlist_count' => $this->_wishlist_count(),
            'notif_count'    => $this->_notification_count(),
            'categories'     => $this->M_product->get_categories()
        ];

        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_wishlist', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    public function toggle_wishlist($product_id) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.']);
            return;
        }

        if ($this->M_wishlist->check_exists($user_id, $product_id)) {
            $this->M_wishlist->remove($user_id, $product_id);
            echo json_encode(['status' => 'removed', 'message' => 'Dihapus dari wishlist.']);
        } else {
            $this->M_wishlist->add($user_id, $product_id);
            echo json_encode(['status' => 'added', 'message' => 'Ditambahkan ke wishlist.']);
        }
    }

    // -------------------------------------------------------
    // LIKE PRODUK (AJAX)
    // -------------------------------------------------------
    public function like_toggle($product_id) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.']);
            return;
        }

        $result = $this->M_product->toggle_like($user_id, (int)$product_id);
        echo json_encode([
            'status'     => 'success',
            'liked'      => $result['liked'],
            'like_count' => $result['like_count']
        ]);
    }

    private function _get_user_wishlist_ids() {
        $user_id = $this->session->userdata('user_id');
        $user_wishlist_ids = [];
        if ($user_id) {
            $wishlist = $this->M_wishlist->get_user_wishlist($user_id);
            foreach ($wishlist as $w) { $user_wishlist_ids[] = $w->product_id; }
        }
        return $user_wishlist_ids;
    }

    // -------------------------------------------------------
    // DIVERSITY BADGES — Assign natural, varied badges
    // -------------------------------------------------------
    /**
     * Memperkaya badge origin pada produk di katalog.
     * Produk yang sudah punya badge dari AI CF tidak diubah.
     * Produk tanpa badge mendapat badge kontekstual berdasarkan data riil.
     * ~30% produk sengaja dibiarkan tanpa badge agar terlihat natural.
     * Badge dari CF (User-Based CF, Item-Based CF, Hybrid CF, Product Interest)
     * TIDAK diubah — hanya produk tanpa badge AI yang mendapat badge kontekstual.
     */
    private function _diversify_badges($products, $existing_origins) {
        if (empty($products)) return $existing_origins;

        $origins = $existing_origins;

        // Hitung berapa badge jenis apa yang sudah ada dari AI CF
        $badge_type_counts = [];
        foreach ($origins as $origin) {
            $badge_type_counts[$origin] = ($badge_type_counts[$origin] ?? 0) + 1;
        }

        // Badge pool: produk tanpa badge AI
        $candidates = [];
        foreach ($products as $p) {
            if (isset($origins[$p->id])) continue; // sudah punya badge dari AI CF
            $candidates[] = $p;
        }

        if (empty($candidates)) return $origins;

        // Semua produk yang memenuhi syarat akan mendapat badge (tidak ada limit 70%)
        foreach ($candidates as $p) {
            $badge = $this->_determine_contextual_badge($p, $badge_type_counts);
            if ($badge) {
                $origins[$p->id] = $badge;
                $badge_type_counts[$badge] = ($badge_type_counts[$badge] ?? 0) + 1;
            }
        }

        return $origins;
    }

    /**
     * Menentukan badge kontekstual untuk satu produk berdasarkan data riilnya.
     * 
     * Aturan badge (non-CF):
     * - Trending Now   → berdasarkan penjualan (total_sold > 0)
     * - Best Seller    → penjualan tertinggi (total_sold >= 3)
     * - Top Rated      → rating >= 4.0 dengan minimal 1 review
     * - Discovery      → fallback untuk produk tanpa sinyal apapun
     *
     * Cap: maks 3 produk per jenis badge per halaman.
     */
    private function _determine_contextual_badge($product, &$type_counts) {
        $max_per_type = 3;

        $rules = [];

        // 1. Best Seller — penjualan tinggi (>= 3 unit terjual)
        if (isset($product->total_sold) && $product->total_sold >= 3) {
            $rules['Best Seller'] = (int) $product->total_sold * 10;
        }

        // 2. Trending Now — ada penjualan (total_sold > 0), bukan stok
        if (isset($product->total_sold) && $product->total_sold > 0 && $product->total_sold < 3) {
            $rules['Trending Now'] = (int) $product->total_sold * 20;
        }

        // 3. Top Rated — rating >= 4.0 dengan minimal 1 review
        if (isset($product->avg_rating) && $product->avg_rating >= 4.0 
            && isset($product->review_count) && $product->review_count >= 1) {
            $rules['Top Rated'] = (int) ($product->avg_rating * 20);
        }

        // Sort by score descending
        arsort($rules);

        // Kembalikan badge terbaik tanpa batasan jumlah per tipe
        foreach ($rules as $badge => $score) {
            return $badge;
        }

        // Jika tidak ada aturan yang terpenuhi, tidak beri badge
        return null;
    }
}
