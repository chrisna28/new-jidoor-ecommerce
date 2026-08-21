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
        $data = [
            'title'      => 'Detail Pesanan #' . $order_id . ' — JiDoor Store',
            'order'      => $order,
            'order_id'   => $order_id,
            'items'      => $this->M_order->get_order_items($order_id),
            'cart_count' => 0,
            'categories' => $this->M_product->get_categories(),
        ];
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_pesanan_detail', $data);
        $this->load->view('frontend/v_footer', $data);
    }
}
