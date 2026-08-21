<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model: M_rating
 * Mengelola rating produk untuk Collaborative Filtering
 */
class M_rating extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Tambah atau update rating user untuk sebuah produk
     */
    public function add_or_update($user_id, $product_id, $rating, $review = null) {
        $existing = $this->check_rated($user_id, $product_id);
        $data = [
            'rating' => $rating,
            'review' => $review
        ];

        if ($existing) {
            $this->db->where('user_id', $user_id);
            $this->db->where('product_id', $product_id);
            return $this->db->update('ratings', $data);
        } else {
            $data['user_id'] = $user_id;
            $data['product_id'] = $product_id;
            return $this->db->insert('ratings', $data);
        }
    }

    /**
     * Cek apakah user sudah memberi rating untuk produk ini
     */
    public function check_rated($user_id, $product_id) {
        return $this->db->get_where('ratings', [
            'user_id'    => $user_id,
            'product_id' => $product_id
        ])->row();
    }

    /**
     * Ambil semua rating milik seorang user
     */
    public function get_user_ratings($user_id) {
        return $this->db->get_where('ratings', ['user_id' => $user_id])->result();
    }

    /**
     * Ambil rata-rata rating untuk sebuah produk
     */
    public function get_avg_rating($product_id) {
        $this->db->select('AVG(rating) as avg_rating, COUNT(*) as total_rating');
        $this->db->where('product_id', $product_id);
        $row = $this->db->get('ratings')->row();
        return $row;
    }

    /**
     * Ambil semua data rating (untuk AI model)
     */
    public function get_all() {
        return $this->db->get('ratings')->result();
    }

    /**
     * Ambil semua review untuk produk tertentu
     */
    public function get_product_reviews($product_id) {
        $this->db->select('r.*, u.username');
        $this->db->from('ratings r');
        $this->db->join('users u', 'u.id = r.user_id');
        $this->db->where('r.product_id', $product_id);
        $this->db->order_by('r.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Ambil semua rating user beserta detail produk (untuk halaman riwayat rating)
     */
    public function get_user_ratings_with_product($user_id) {
        $this->db->select('r.*, p.name as product_name, p.slug as product_slug, p.image as product_image, p.price as product_price');
        $this->db->from('ratings r');
        $this->db->join('products p', 'p.id = r.product_id');
        $this->db->where('r.user_id', $user_id);
        $this->db->order_by('r.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Ambil semua rating dengan detail user dan produk (untuk admin)
     */
    public function get_all_with_details($limit = 10, $offset = 0, $rating = null) {
        $this->db->select('r.*, u.username, p.name as product_name');
        $this->db->from('ratings r');
        $this->db->join('users u', 'u.id = r.user_id');
        $this->db->join('products p', 'p.id = r.product_id');
        
        if ($rating !== null && $rating !== '') {
            $this->db->where('r.rating', $rating);
        }
        
        $this->db->order_by('r.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    /**
     * Hitung total semua rating
     */
    public function count_all($rating = null) {
        if ($rating !== null && $rating !== '') {
            $this->db->where('rating', $rating);
        }
        return $this->db->count_all_results('ratings');
    }

    /**
     * Hapus rating berdasarkan ID
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('ratings');
    }
}
