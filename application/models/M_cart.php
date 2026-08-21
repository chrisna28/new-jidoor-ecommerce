<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model: M_cart
 * Mengelola keranjang belanja (tersimpan di database)
 */
class M_cart extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Ambil isi keranjang user beserta detail produk
     */
    public function get_cart($user_id) {
        $this->db->select('c.id, c.qty, c.created_at, p.id as product_id, p.name, p.price, p.stock, p.image, p.slug');
        $this->db->from('cart c');
        $this->db->join('products p', 'p.id = c.product_id', 'left');
        $this->db->where('c.user_id', $user_id);
        $this->db->order_by('c.created_at', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Hitung total harga keranjang
     */
    public function get_cart_total($user_id) {
        $this->db->select('SUM(p.price * c.qty) as total');
        $this->db->from('cart c');
        $this->db->join('products p', 'p.id = c.product_id', 'left');
        $this->db->where('c.user_id', $user_id);
        $row = $this->db->get()->row();
        return $row ? (float)$row->total : 0;
    }

    /**
     * Hitung jumlah item di keranjang
     */
    public function count_cart($user_id) {
        return $this->db->where('user_id', $user_id)->count_all_results('cart');
    }

    /**
     * Cek apakah produk sudah ada di keranjang
     */
    public function get_existing($user_id, $product_id) {
        return $this->db->get_where('cart', [
            'user_id'    => $user_id,
            'product_id' => $product_id
        ])->row();
    }

    /**
     * Tambah item ke keranjang
     */
    public function add($user_id, $product_id, $qty = 1) {
        $existing = $this->get_existing($user_id, $product_id);
        if ($existing) {
            // Sudah ada → tambah qty
            $this->db->where('id', $existing->id);
            return $this->db->update('cart', ['qty' => $existing->qty + $qty]);
        } else {
            // Belum ada → insert baru
            return $this->db->insert('cart', [
                'user_id'    => $user_id,
                'product_id' => $product_id,
                'qty'        => $qty
            ]);
        }
    }

    /**
     * Update qty item di keranjang
     */
    public function update_qty($cart_id, $qty) {
        $this->db->where('id', $cart_id);
        return $this->db->update('cart', ['qty' => $qty]);
    }

    /**
     * Ambil detail satu item di keranjang
     */
    public function get_item($cart_id) {
        return $this->db->get_where('cart', ['id' => $cart_id])->row();
    }

    /**
     * Hapus satu item dari keranjang
     */
    public function remove($cart_id, $user_id) {
        $this->db->where('id', $cart_id);
        $this->db->where('user_id', $user_id);
        return $this->db->delete('cart');
    }

    /**
     * Kosongkan seluruh keranjang user (setelah checkout)
     */
    public function clear($user_id) {
        $this->db->where('user_id', $user_id);
        return $this->db->delete('cart');
    }
}
