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
        $variant_id = (int)$this->input->post('variant_id');
        $note       = $this->input->post('note', TRUE);
        if ($note !== null) {
            $note = substr($note, 0, 200); // batas catatan
        }
        // Teks permintaan custom (Revisi #4), maks 500 karakter
        $custom_text = $this->input->post('custom_text', TRUE);
        if ($custom_text !== null) {
            $custom_text = substr(trim($custom_text), 0, 500);
        }

        $product = $this->M_product->get_by_id($product_id);
        if (!$product) { redirect('katalog'); }

        // Validasi varian milik produk ini (Revisi #2)
        $variant = null;
        if ($variant_id > 0) {
            $variant = $this->M_product->get_variant($variant_id);
            if (!$variant || $variant->product_id != $product_id) {
                $this->session->set_flashdata('error', 'Varian tidak valid.');
                redirect('produk/' . $product->slug);
            }
        }

        // Stok dicek per varian jika ada, jika tidak pakai stok produk
        $available_stock = $variant ? (int)$variant->stock : (int)$product->stock;
        if ($available_stock < $qty) {
            $this->session->set_flashdata('error', 'Stok tidak mencukupi. Sisa ' . $available_stock . ' unit.');
            redirect('produk/' . $product->slug);
        }

        $this->M_cart->add($user_id, $product_id, $qty, $variant_id > 0 ? $variant_id : NULL, $note, $custom_text);
        $this->session->set_flashdata('success', '"' . $product->name . '" ditambahkan ke keranjang!');
        redirect('keranjang');
    }

    public function update() {
        $cart_id = (int)$this->input->post('cart_id');
        $action  = $this->input->post('action');
        $item    = $this->M_cart->get_item($cart_id);

        if ($item && $item->user_id == $this->_user_id()) {
            // Batas atas qty = stok varian (atau stok produk)
            $max_qty = PHP_INT_MAX;
            if ($item->variant_id) {
                $variant = $this->M_product->get_variant($item->variant_id);
                if ($variant) $max_qty = (int)$variant->stock;
            } else {
                $product = $this->M_product->get_by_id($item->product_id);
                if ($product) $max_qty = (int)$product->stock;
            }

            $new_qty = $item->qty;
            if ($action === 'plus') {
                $new_qty++;
            } elseif ($action === 'minus' && $new_qty > 1) {
                $new_qty--;
            }
            $new_qty = min($new_qty, $max_qty);

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

        // Upload gambar referensi custom per item (Revisi #4)
        $custom_uploads = $this->_handle_custom_uploads($order_id, $items);
        if ($custom_uploads === FALSE) {
            // Validasi custom gagal → hapus order yatim & kembali ke checkout
            $this->M_order->delete_order($order_id);
            redirect('checkout');
        }

        $order_items = [];
        foreach ($items as $item) {
            $order_items[] = [
                'order_id'     => $order_id,
                'product_id'   => $item->product_id,
                'variant_id'   => $item->variant_id,
                'color'        => $item->color,
                'size'         => $item->size,
                'note'         => $item->note,
                'custom_image' => isset($custom_uploads[$item->id]) ? $custom_uploads[$item->id] : NULL,
                'custom_text'  => $item->custom_text,
                'qty'          => $item->qty,
                'price'        => $item->price, // harga efektif (produk + delta varian)
            ];
            // Stok dikurangi per varian jika ada, jika tidak per produk
            if ($item->variant_id) {
                $this->M_product->reduce_variant_stock($item->variant_id, $item->qty);
            } else {
                $this->M_product->reduce_stock($item->product_id, $item->qty);
            }
        }
        $this->M_order->add_order_items($order_items);
        // Riwayat tracking pertama (Revisi #5)
        $this->M_order->update_status($order_id, 'pending', 'Pesanan dibuat, menunggu pembayaran');
        $this->M_cart->clear($user_id);

        // Metode bayar online → arahkan ke halaman detail untuk popup Snap (Revisi #6)
        if ($this->input->post('payment_method') === 'midtrans') {
            redirect('pesanan/detail/' . $order_id);
        }

        $this->session->set_flashdata('success', 'Pesanan berhasil! Silakan upload bukti pembayaran.');
        redirect('checkout/bukti/' . $order_id);
    }

    /**
     * Proses unggahan gambar referensi custom (Revisi #4).
     * Aturan: produk custom minimal punya teks ATAU gambar.
     * Return: [cart_item_id => relative_path] atau FALSE jika validasi gagal.
     */
    private function _handle_custom_uploads($order_id, $items) {
        $result = [];

        foreach ($items as $item) {
            if (empty($item->is_custom)) { continue; }

            $has_file = isset($_FILES['custom_image']['name'][$item->id]) && $_FILES['custom_image']['name'][$item->id] !== '';
            $has_text = !empty($item->custom_text);

            if (!$has_file && !$has_text) {
                $this->session->set_flashdata('error', 'Produk "' . $item->name . '" adalah produk custom — isi permintaan teks atau unggah gambar referensi.');
                return FALSE;
            }

            if (!$has_file) { continue; }

            // Susun ulang array $_FILES agar kompatibel dengan library upload CI
            $_FILES['file']['name']     = $_FILES['custom_image']['name'][$item->id];
            $_FILES['file']['type']     = $_FILES['custom_image']['type'][$item->id];
            $_FILES['file']['tmp_name'] = $_FILES['custom_image']['tmp_name'][$item->id];
            $_FILES['file']['error']    = $_FILES['custom_image']['error'][$item->id];
            $_FILES['file']['size']     = $_FILES['custom_image']['size'][$item->id];

            $dir = './uploads/custom/' . $order_id . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, TRUE);
            }

            $config = [
                'upload_path'   => $dir,
                'allowed_types' => 'jpg|jpeg|png',
                'max_size'      => 2048,
                'encrypt_name'  => TRUE,
            ];
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {
                $result[$item->id] = 'uploads/custom/' . $order_id . '/' . $this->upload->data('file_name');
            } else {
                $this->session->set_flashdata('error', 'Gagal upload gambar custom "' . $item->name . '": ' . $this->upload->display_errors('', ''));
                return FALSE;
            }
        }

        return $result;
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
