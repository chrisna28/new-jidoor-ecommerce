<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller: Cart
 * Mengelola keranjang belanja dan proses checkout
 */
class Cart extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model(['M_cart', 'M_product', 'M_order']);
        if (!$this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Silakan login terlebih dahulu.');
            redirect('login');
        }
    }

    private function _user_id() { return $this->session->userdata('user_id'); }

    public function index() {
        $user_id = $this->_user_id();
        $items   = $this->M_cart->get_cart($user_id);
        $data = [
            'title'       => 'Keranjang Belanja — JiDoor Store',
            'cart_items'  => $items,
            'total_price' => $this->M_cart->get_cart_total($user_id),
            'cart_count'  => count($items),
            'categories'  => $this->M_product->get_categories(),
        ];
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_cart', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    public function add() {
        $user_id    = $this->_user_id();
        $product_id = (int)$this->input->post('product_id');
        $qty        = max(1, (int)$this->input->post('qty'));
        $product    = $this->M_product->get_by_id($product_id);
        if (!$product) { redirect('katalog'); }
        if ($product->stock < $qty) {
            $this->session->set_flashdata('error', 'Stok tidak mencukupi.');
            redirect('produk/' . $product->slug);
        }
        $this->M_cart->add($user_id, $product_id, $qty);
        $this->session->set_flashdata('success', '"' . $product->name . '" ditambahkan ke keranjang!');
        redirect('keranjang');
    }

    public function update() {
        $cart_id = (int)$this->input->post('cart_id');
        $action  = $this->input->post('action');
        $item    = $this->M_cart->get_item($cart_id);

        if ($item) {
            $new_qty = $item->qty;
            if ($action === 'plus') {
                $new_qty++;
            } elseif ($action === 'minus' && $new_qty > 1) {
                $new_qty--;
            }
            $this->M_cart->update_qty($cart_id, $new_qty);
        }
        redirect('keranjang');
    }

    public function remove($cart_id) {
        $this->M_cart->remove((int)$cart_id, $this->_user_id());
        $this->session->set_flashdata('success', 'Item dihapus dari keranjang.');
        redirect('keranjang');
    }

    public function checkout() {
        $user_id = $this->_user_id();
        $items   = $this->M_cart->get_cart($user_id);
        if (empty($items)) { redirect('keranjang'); }
        $data = [
            'title'       => 'Checkout — JiDoor Store',
            'cart_items'  => $items,
            'total_price' => $this->M_cart->get_cart_total($user_id),
            'cart_count'  => count($items),
            'categories'  => $this->M_product->get_categories(),
        ];
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_checkout', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    public function process_checkout() {
        $user_id = $this->_user_id();
        $items   = $this->M_cart->get_cart($user_id);
        if (empty($items)) { redirect('keranjang'); }
        
        $receiver_name = $this->input->post('receiver_name', TRUE);
        $phone         = $this->input->post('phone', TRUE);
        $address       = $this->input->post('address', TRUE);
        $city          = $this->input->post('city', TRUE);
        $province      = $this->input->post('province', TRUE);

        if (empty($receiver_name) || empty($phone) || empty($address)) {
            $this->session->set_flashdata('error', 'Semua data pengiriman wajib diisi.');
            redirect('checkout');
        }

        $order_id = $this->M_order->create_order([
            'user_id'       => $user_id,
            'receiver_name' => $receiver_name,
            'phone'         => $phone,
            'total_price'   => $this->M_cart->get_cart_total($user_id),
            'status'        => 'pending',
            'address'       => $address,
            'city'          => $city,
            'province'      => $province,
            'note'          => $this->input->post('note', TRUE)
        ]);
        $order_items = [];
        foreach ($items as $item) {
            $order_items[] = ['order_id' => $order_id, 'product_id' => $item->product_id, 'qty' => $item->qty, 'price' => $item->price];
            $this->M_product->reduce_stock($item->product_id, $item->qty);
        }
        $this->M_order->add_order_items($order_items);
        $this->M_cart->clear($user_id);
        $this->session->set_flashdata('success', 'Pesanan berhasil! Silakan upload bukti pembayaran.');
        redirect('checkout/bukti/' . $order_id);
    }

    public function upload_bukti($order_id) {
        $user_id = $this->_user_id();
        $order   = $this->M_order->get_order_detail($order_id);
        if (!$order || $order->user_id != $user_id) { redirect('pesanan'); }
        
        $data = [
            'title'       => 'Upload Bukti Pembayaran',
            'order'       => $order,
            'order_id'    => $order_id,
            'items'       => $this->M_order->get_order_items($order_id),
            'cart_count'  => 0,
            'categories'  => $this->M_product->get_categories(),
        ];
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_upload_bukti', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    public function upload_bukti_aksi() {
        $user_id  = $this->_user_id();
        $order_id = $this->input->post('order_id');
        $order    = $this->M_order->get_order_detail($order_id);

        if (!$order || $order->user_id != $user_id) { redirect('pesanan'); }

        if (!empty($_FILES['payment_proof']['name'])) {
            // Pastikan direktori ada
            if (!is_dir('./uploads/payments/')) {
                mkdir('./uploads/payments/', 0777, TRUE);
            }

            $config = [
                'upload_path'   => './uploads/payments/',
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size'      => 2048,
                'file_name'     => 'bukti_' . $order_id . '_' . time()
            ];
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('payment_proof')) {
                $file = $this->upload->data();
                $this->M_order->update_payment_proof($order_id, $file['file_name']);
                $this->session->set_flashdata('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.');
                redirect('pesanan/detail/' . $order_id);
            } else {
                $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                redirect('checkout/bukti/' . $order_id);
            }
        } else {
            $this->session->set_flashdata('error', 'Silakan pilih file bukti pembayaran.');
            redirect('checkout/bukti/' . $order_id);
        }
    }
}
