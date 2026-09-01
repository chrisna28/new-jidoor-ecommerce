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
     * Ambil isi keranjang user beserta detail produk, varian, lengan & bahan.
     * Harga efektif = harga produk + delta varian + delta lengan
     *                + harga kain bahan + harga sablon bahan.
     */
    public function get_cart($user_id) {
        $this->db->select("c.id, c.qty, c.note, c.variant_id, c.custom_text, c.sleeve_id, c.material_id,
            p.id as product_id, p.name, p.price, p.stock, p.image, p.slug, p.is_custom,
            p.variant_name1, p.variant_name2,
            v.color, v.size, v.stock as variant_stock,
            s.name as sleeve, s.price_delta as sleeve_delta,
            m.name as material, m.fabric_price, m.sablon_price,
            (p.price + COALESCE(v.price_delta, 0) + COALESCE(s.price_delta, 0)
             + COALESCE(m.fabric_price, 0) + COALESCE(m.sablon_price, 0)) as price", FALSE);
        $this->db->from('cart c');
        $this->db->join('products p', 'p.id = c.product_id', 'left');
        $this->db->join('product_variants v', 'v.id = c.variant_id', 'left');
        $this->db->join('custom_sleeves s', 's.id = c.sleeve_id', 'left');
        $this->db->join('custom_materials m', 'm.id = c.material_id', 'left');
        $this->db->where('c.user_id', $user_id);
        $this->db->order_by('c.created_at', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Hitung total harga keranjang (harga produk + delta varian + lengan + bahan)
     */
    public function get_cart_total($user_id) {
        $this->db->select('SUM((p.price + COALESCE(v.price_delta, 0) + COALESCE(s.price_delta, 0)
            + COALESCE(m.fabric_price, 0) + COALESCE(m.sablon_price, 0)) * c.qty) as total', FALSE);
        $this->db->from('cart c');
        $this->db->join('products p', 'p.id = c.product_id', 'left');
        $this->db->join('product_variants v', 'v.id = c.variant_id', 'left');
        $this->db->join('custom_sleeves s', 's.id = c.sleeve_id', 'left');
        $this->db->join('custom_materials m', 'm.id = c.material_id', 'left');
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
     * Cek apakah kombinasi produk+varian+lengan+bahan sudah ada di keranjang
     */
    public function get_existing($user_id, $product_id, $variant_id = NULL, $sleeve_id = NULL, $material_id = NULL) {
        $this->db->where('user_id', $user_id);
        $this->db->where('product_id', $product_id);
        if ($variant_id) {
            $this->db->where('variant_id', $variant_id);
        } else {
            $this->db->where('(variant_id IS NULL OR variant_id = 0)');
        }
        if ($sleeve_id) {
            $this->db->where('sleeve_id', $sleeve_id);
        } else {
            $this->db->where('(sleeve_id IS NULL OR sleeve_id = 0)');
        }
        if ($material_id) {
            $this->db->where('material_id', $material_id);
        } else {
            $this->db->where('(material_id IS NULL OR material_id = 0)');
        }
        return $this->db->get('cart')->row();
    }

    /**
     * Tambah item ke keranjang (unik per user + produk + varian + lengan + bahan)
     */
    public function add($user_id, $product_id, $qty = 1, $variant_id = NULL, $note = NULL, $custom_text = NULL, $sleeve_id = NULL, $material_id = NULL) {
        $existing = $this->get_existing($user_id, $product_id, $variant_id, $sleeve_id, $material_id);
        if ($existing) {
            // Sudah ada → gabung qty, catatan/teks custom terbaru menang
            $data = ['qty' => $existing->qty + $qty];
            if ($note !== null && $note !== '') {
                $data['note'] = $note;
            }
            if ($custom_text !== null && $custom_text !== '') {
                $data['custom_text'] = $custom_text;
            }
            $this->db->where('id', $existing->id);
            return $this->db->update('cart', $data);
        } else {
            // Belum ada → insert baru
            return $this->db->insert('cart', [
                'user_id'     => $user_id,
                'product_id'  => $product_id,
                'variant_id'  => $variant_id ?: NULL,
                'sleeve_id'   => $sleeve_id ?: NULL,
                'material_id' => $material_id ?: NULL,
                'qty'         => $qty,
                'note'        => $note ?: NULL,
                'custom_text' => $custom_text ?: NULL,
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
