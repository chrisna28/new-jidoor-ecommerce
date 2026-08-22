<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller: Order
 * Riwayat dan detail pesanan milik user
 */
class Order extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model(['M_order', 'M_product']);
        if (!$this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Silakan login terlebih dahulu.');
            redirect('login');
        }
    }

    /**
     * Riwayat pesanan user
     */
    public function riwayat() {
        $user_id = $this->session->userdata('user_id');
        $data = [
            'title'      => 'Riwayat Pesanan — JiDoor Store',
            'orders'     => $this->M_order->get_orders($user_id),
            'cart_count' => 0,
            'categories' => $this->M_product->get_categories(),
        ];
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_riwayat', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    /**
     * Detail pesanan
     */
    public function detail($order_id) {
        $user_id = $this->session->userdata('user_id');
        $order   = $this->M_order->get_order_detail($order_id);
        if (!$order || $order->user_id != $user_id) { redirect('pesanan'); }

        // Client key untuk snap.js di view (Revisi #6)
        require_once APPPATH . 'config/midtrans.php';

        $data = [
            'title'      => 'Detail Pesanan #' . $order_id . ' — JiDoor Store',
            'order'      => $order,
            'order_id'   => $order_id,
            'items'      => $this->M_order->get_order_items($order_id),
            'tracking'   => $this->M_order->get_tracking($order_id),
            'cart_count' => 0,
            'categories' => $this->M_product->get_categories(),
        ];
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_pesanan_detail', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    /**
     * Customer konfirmasi barang diterima: shipped -> delivered (Revisi #5)
     */
    public function terima($order_id) {
        $user_id = $this->session->userdata('user_id');
        $order   = $this->M_order->get_order_detail($order_id);
        if (!$order || $order->user_id != $user_id) { redirect('pesanan'); }

        if ($order->status !== 'shipped') {
            $this->session->set_flashdata('error', 'Pesanan ini belum dalam status dikirim.');
            redirect('pesanan/detail/' . $order_id);
        }

        $this->M_order->update_status($order_id, 'delivered', 'Pesanan diterima oleh pelanggan');
        $this->session->set_flashdata('success', 'Terima kasih! Pesanan ditandai sudah diterima.');
        redirect('pesanan/detail/' . $order_id);
    }

    // =======================================================
    // MIDTRANS SNAP (Revisi #6)
    // =======================================================

    /**
     * Inisialisasi konfigurasi library Midtrans
     */
    private function _init_midtrans() {
        require_once APPPATH . 'config/midtrans.php';
        \Midtrans\Config::$serverKey    = MIDTRANS_SERVER_KEY;
        \Midtrans\Config::$isProduction = MIDTRANS_IS_PRODUCTION;
        \Midtrans\Config::$isSanitized  = TRUE;
        \Midtrans\Config::$is3ds        = TRUE;
    }

    /**
     * Susun item_details dari order_items untuk payload Snap
     */
    private function _build_item_details($order_id) {
        $items   = [];
        foreach ($this->M_order->get_order_items($order_id) as $it) {
            $name = $it->name;
            if (!empty($it->color) && $it->color !== 'Standar') { $name .= ' (' . $it->color; }
            if (!empty($it->size) && $it->size !== 'Standar')  { $name .= (substr_count($name, '(') ? ' / ' : ' (') . $it->size; }
            if (strpos($name, '(') !== false && strpos($name, ')') === false) { $name .= ')'; }
            $items[] = [
                'id'       => 'ITEM-' . $it->product_id,
                'price'    => (int) $it->price,
                'quantity' => (int) $it->qty,
                'name'     => substr($name, 0, 50),
            ];
        }
        return $items;
    }

    /**
     * Endpoint AJAX: buat Snap token untuk popup pembayaran
     */
    public function bayar_midtrans($order_id) {
        $user_id = $this->session->userdata('user_id');
        $order   = $this->M_order->get_order_detail($order_id);
        if (!$order || $order->user_id != $user_id || $order->status !== 'pending') {
            show_404();
        }
        $this->_init_midtrans();

        $mt_order_id = 'JIDOOR-' . $order_id . '-' . time(); // harus unik per transaksi
        $params = [
            'transaction_details' => [
                'order_id'     => $mt_order_id,
                'gross_amount' => (int) $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $order->receiver_name ?: $this->session->userdata('username'),
                'email'      => $this->session->userdata('email'),
                'phone'      => $order->phone,
            ],
            'item_details' => $this->_build_item_details($order_id),
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $this->M_order->save_snap_token($order_id, $snapToken, $mt_order_id);
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode(['token' => $snapToken]));
        } catch (Exception $e) {
            $this->output
                 ->set_status_header(500)
                 ->set_content_type('application/json')
                 ->set_output(json_encode(['error' => $e->getMessage()]));
        }
    }

    /**
     * Redirect tujuan setelah popup Snap sukses.
     * Verifikasi ulang ke API Get Status Midtrans — cocok untuk demo lokal
     * tanpa webhook/ngrok.
     */
    public function midtrans_finish($order_id) {
        $user_id = $this->session->userdata('user_id');
        $order   = $this->M_order->get_order_detail($order_id);
        if (!$order || $order->user_id != $user_id) { redirect('pesanan'); }
        if (empty($order->midtrans_order_id)) { redirect('pesanan/detail/' . $order_id); }

        $this->_init_midtrans();
        try {
            // Verifikasi ulang ke API Midtrans memakai transaction order_id
            // yang tersimpan saat token dibuat (anti-forgery).
            $status = \Midtrans\Transaction::status($order->midtrans_order_id);

            if (in_array($status->transaction_status, ['capture', 'settlement'])) {
                $this->M_order->mark_paid_if_pending($order_id);
                $this->session->set_flashdata('success', 'Pembayaran online berhasil! Pesanan sedang diproses.');
            } elseif (in_array($status->transaction_status, ['deny', 'cancel', 'expire'])) {
                $this->M_order->mark_cancelled_if_pending($order_id);
                $this->session->set_flashdata('error', 'Pembayaran tidak selesai. Silakan coba lagi.');
            } else {
                $this->session->set_flashdata('info', 'Pembayaran masih menunggu: ' . $status->transaction_status);
            }
        } catch (Exception $e) {
            log_message('error', 'Midtrans finish: ' . $e->getMessage());
            $this->session->set_flashdata('info', 'Status pembayaran belum dapat dikonfirmasi otomatis. Jika sudah membayar, mohon tunggu sebentar lalu muat ulang halaman.');
        }
        redirect('pesanan/detail/' . $order_id);
    }
}
