<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model: M_order
 * Mengelola pesanan & detail item
 */
class M_order extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Buat order baru, kembalikan order_id
     */
    public function create_order($data) {
        $this->db->insert('orders', $data);
        return $this->db->insert_id();
    }

    /**
     * Tambahkan item ke order
     */
    public function add_order_items($items) {
        // $items = array of ['order_id', 'product_id', 'qty', 'price']
        return $this->db->insert_batch('order_items', $items);
    }

    /**
     * Ambil semua pesanan milik seorang user
     */
    public function get_orders($user_id) {
        $this->db->select('o.*, u.username, u.email');
        $this->db->from('orders o');
        $this->db->join('users u', 'u.id = o.user_id', 'left');
        $this->db->where('o.user_id', $user_id);
        $this->db->order_by('o.id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Ambil semua pesanan (untuk admin)
     */
    public function get_all_orders($status = null, $limit = null, $offset = 0) {
        $this->db->select('o.*, u.username, u.email, u.phone');
        $this->db->from('orders o');
        $this->db->join('users u', 'u.id = o.user_id', 'left');
        if ($status) {
            $this->db->where('o.status', $status);
        }
        $this->db->order_by('o.id', 'DESC');
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    /**
     * Ambil detail order beserta item-itemnya
     */
    public function get_order_detail($order_id) {
        return $this->db->get_where('orders', ['id' => $order_id])->row();
    }

    /**
     * Ambil item-item dalam satu order
     */
    public function get_order_items($order_id) {
        $this->db->select('oi.*, p.name, p.image, p.slug, p.variant_name1, p.variant_name2');
        $this->db->from('order_items oi');
        $this->db->join('products p', 'p.id = oi.product_id', 'left');
        $this->db->where('oi.order_id', $order_id);
        return $this->db->get()->result();
    }

    /**
     * Update status pesanan + catat riwayat tracking (Revisi #5)
     */
    public function update_status($order_id, $status, $keterangan = NULL, $resi = NULL, $courier = NULL) {
        $this->db->trans_start();

        $data = ['status' => $status];
        if ($resi)    { $data['resi']    = $resi; }
        if ($courier) { $data['courier'] = $courier; }
        $this->db->where('id', $order_id);
        $this->db->update('orders', $data);

        $this->db->insert('order_tracking', [
            'order_id'    => $order_id,
            'status'      => $status,
            'description' => $keterangan,
            'resi'        => $resi,
            'courier'     => $courier,
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Riwayat tracking pesanan (urut waktu naik)
     */
    public function get_tracking($order_id) {
        return $this->db->where('order_id', $order_id)
                        ->order_by('created_at', 'ASC')
                        ->get('order_tracking')->result();
    }

    /**
     * Hapus order beserta itemnya (untuk pembatalan saat validasi checkout gagal)
     */
    public function delete_order($order_id) {
        $this->db->trans_start();
        $this->db->where('order_id', $order_id)->delete('order_items');
        $this->db->where('id', $order_id)->delete('orders');
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // =======================================================
    // MIDTRANS SNAP (Revisi #6)
    // =======================================================

    /**
     * Simpan snap token + transaction order_id Midtrans
     */
    public function save_snap_token($order_id, $token, $midtrans_order_id = NULL) {
        $this->db->where('id', $order_id);
        return $this->db->update('orders', [
            'snap_token'        => $token,
            'midtrans_order_id' => $midtrans_order_id,
        ]);
    }

    /**
     * Tandai paid HANYA jika masih pending (idempotent — aman dipanggil
     * berulang dari webhook + finish redirect)
     */
    public function mark_paid_if_pending($order_id) {
        $order = $this->get_order_detail($order_id);
        if (!$order || $order->status !== 'pending') {
            return FALSE; // sudah diproses sebelumnya
        }
        return $this->update_status($order_id, 'paid', 'Pembayaran online diverifikasi via Midtrans');
    }

    /**
     * Batalkan HANYA jika masih pending (deny/cancel/expire)
     */
    public function mark_cancelled_if_pending($order_id) {
        $order = $this->get_order_detail($order_id);
        if (!$order || $order->status !== 'pending') {
            return FALSE;
        }
        return $this->update_status($order_id, 'cancelled', 'Pembayaran online gagal/kedaluwarsa');
    }

    /**
     * Update bukti pembayaran
     */
    public function update_payment_proof($order_id, $filename) {
        $this->db->where('id', $order_id);
        return $this->db->update('orders', ['payment_proof' => $filename]);
    }

    /**
     * Hitung pesanan berdasarkan status
     */
    public function count_by_status($status) {
        return $this->db->where('status', $status)->count_all_results('orders');
    }

    /**
     * Hitung total semua pesanan
     */
    public function count_all() {
        return $this->db->count_all('orders');
    }

    /**
     * Total revenue dari pesanan paid/shipped
     */
    public function get_total_revenue() {
        $this->db->select('SUM(total_price) as revenue');
        $this->db->from('orders');
        $this->db->where_in('status', ['paid', 'shipped']);
        $row = $this->db->get()->row();
        return $row ? (float)$row->revenue : 0;
    }

    /**
     * Revenue per bulan (12 bulan terakhir) untuk chart admin
     */
    public function get_revenue_by_month() {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month_key, 
                       DATE_FORMAT(created_at, '%b %Y') as month_label, 
                       SUM(total_price) as revenue,
                       COUNT(*) as order_count
                FROM orders 
                WHERE status IN ('paid','shipped') 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY month_key, month_label
                ORDER BY month_key ASC";
        return $this->db->query($sql)->result();
    }

    /**
     * Hitung pesanan dengan status berubah untuk user (notifikasi)
     */
    public function count_user_active_orders($user_id) {
        $this->db->from('orders');
        $this->db->where('user_id', $user_id);
        $this->db->where_in('status', ['paid', 'shipped']);
        return $this->db->count_all_results();
    }

    /**
     * Ambil Top 5 Produk Terlaris
     */
    public function get_top_selling_products($limit = 5) {
        $this->db->select('p.id, p.name, p.image, p.price, c.name as category_name, SUM(oi.qty) as total_sold');
        $this->db->from('order_items oi');
        $this->db->join('products p', 'p.id = oi.product_id', 'left');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('orders o', 'o.id = oi.order_id', 'left');
        $this->db->where_in('o.status', ['paid', 'shipped']);
        $this->db->group_by('p.id');
        $this->db->order_by('total_sold', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }
}
