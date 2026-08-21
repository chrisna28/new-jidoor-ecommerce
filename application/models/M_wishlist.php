<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_wishlist extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_user_wishlist($user_id) {
        $this->db->select('w.*, p.name, p.slug, p.price, p.image, COALESCE(AVG(r.rating), 0) as avg_rating, COALESCE(oi_sub.total_sold, 0) as total_sold, c.name as category_name');
        $this->db->from('wishlists w');
        $this->db->join('products p', 'p.id = w.product_id', 'left');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('ratings r', 'r.product_id = p.id', 'left');
        $this->db->join('(SELECT product_id, SUM(qty) as total_sold FROM order_items GROUP BY product_id) oi_sub', 'oi_sub.product_id = p.id', 'left');
        $this->db->where('w.user_id', $user_id);
        $this->db->group_by('w.id');
        $this->db->order_by('w.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function check_exists($user_id, $product_id) {
        return $this->db->get_where('wishlists', [
            'user_id' => $user_id,
            'product_id' => $product_id
        ])->num_rows() > 0;
    }

    public function add($user_id, $product_id) {
        if (!$this->check_exists($user_id, $product_id)) {
            return $this->db->insert('wishlists', [
                'user_id' => $user_id,
                'product_id' => $product_id
            ]);
        }
        return false;
    }

    public function remove($user_id, $product_id) {
        return $this->db->delete('wishlists', [
            'user_id' => $user_id,
            'product_id' => $product_id
        ]);
    }

    public function count_wishlist($user_id) {
        $this->db->where('user_id', $user_id);
        return $this->db->count_all_results('wishlists');
    }
}
