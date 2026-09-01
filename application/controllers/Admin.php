<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller: Admin
 * Dashboard Admin — CRUD Produk, Manajemen Pesanan, Verifikasi Pembayaran
 */
class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model(['M_product', 'M_order', 'M_user', 'M_rating', 'M_chat']);
        $this->load->library('chat_token');
        // Middleware: wajib login sebagai admin
        if ($this->session->userdata('role') !== 'admin') {
            redirect('login');
        }
    }

    // -------------------------------------------------------
    // DASHBOARD
    // -------------------------------------------------------
    public function index() {
        $data = [
            'title'          => 'Dashboard Admin — JiDoor Store',
            'total_products' => $this->M_product->count_all(),
            'total_orders'   => $this->M_order->count_all(),
            'pending_orders' => $this->M_order->count_by_status('pending'),
            'paid_orders'    => $this->M_order->count_by_status('paid'),
            'total_revenue'  => $this->M_order->get_total_revenue(),
            'total_users'    => $this->M_user->count_users(),
            'recent_orders'  => $this->M_order->get_all_orders(null, 5),
            'top_selling'    => $this->M_order->get_top_selling_products(5),
            'category_dist'  => $this->M_product->get_category_distribution(),
            'revenue_chart'  => $this->M_order->get_revenue_by_month(),
            'low_variants'   => $this->M_product->get_low_stock_variants(5),
            'low_simple'     => $this->M_product->get_low_stock_simple(5),
            'unread_chat'    => $this->M_chat->count_unread_admin(),
            'active_tab'     => 'dashboard',
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_index', $data);
        $this->load->view('admin/v_footer', $data);
    }

    // -------------------------------------------------------
    // MANAJEMEN RATING
    // -------------------------------------------------------
    public function ratings($offset = 0) {
        $per_page = 10;
        $rating_filter = $this->input->get('rating');
        
        $this->load->library('pagination');

        $config['base_url']   = base_url('admin/ratings/');
        $config['total_rows'] = $this->M_rating->count_all($rating_filter);
        $config['per_page']   = $per_page;
        $config['uri_segment'] = 3;
        $config['reuse_query_string'] = TRUE;

        // Styling (Same as others)
        $config['full_tag_open']  = '<nav><ul class="pagination pagination-sm justify-content-center gap-2 border-0">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['num_tag_open']   = '<li class="page-item">';
        $config['num_tag_close']  = '</li>';
        $config['cur_tag_open']   = '<li class="page-item active"><span class="page-link bg-admin-primary border-0 rounded-3">';
        $config['cur_tag_close']  = '</span></li>';
        $config['next_tag_open']  = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open']  = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['attributes']     = array('class' => 'page-link bg-admin-card border-secondary border-opacity-20 text-white rounded-3');

        $this->pagination->initialize($config);

        $data = [
            'title'           => 'Manajemen Rating & Review',
            'ratings'         => $this->M_rating->get_all_with_details($per_page, $offset, $rating_filter),
            'active_tab'      => 'ratings',
            'selected_rating' => $rating_filter,
            'pagination'      => $this->pagination->create_links(),
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_ratings', $data);
        $this->load->view('admin/v_footer', $data);
    }

    public function rating_hapus($id) {
        $this->M_rating->delete($id);
        $this->session->set_flashdata('success', 'Rating berhasil dihapus.');
        redirect('admin/ratings');
    }

    // -------------------------------------------------------
    // MANAJEMEN PRODUK
    // -------------------------------------------------------
    public function produk($offset = 0) {
        $per_page = 10;
        $this->load->library('pagination');

        $config['base_url']   = base_url('admin/produk/');
        $config['total_rows'] = $this->M_product->count_all();
        $config['per_page']   = $per_page;
        $config['uri_segment'] = 3;

        // Modern Dark Pagination Styling
        $config['full_tag_open']  = '<nav><ul class="pagination pagination-sm justify-content-center gap-2 border-0">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['num_tag_open']   = '<li class="page-item">';
        $config['num_tag_close']  = '</li>';
        $config['cur_tag_open']   = '<li class="page-item active"><span class="page-link bg-admin-primary border-0 rounded-3">';
        $config['cur_tag_close']  = '</span></li>';
        $config['next_tag_open']  = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open']  = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['attributes']     = array('class' => 'page-link bg-admin-card border-secondary border-opacity-20 text-white rounded-3');

        $this->pagination->initialize($config);

        $data = [
            'title'      => 'Manajemen Produk',
            'products'   => $this->M_product->get_all($per_page, $offset),
            'categories' => $this->M_product->get_categories(),
            'active_tab' => 'produk',
            'pagination' => $this->pagination->create_links(),
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_produk', $data);
        $this->load->view('admin/v_footer', $data);
    }

    public function produk_tambah() {
        $data = [
            'title'      => 'Tambah Produk',
            'categories' => $this->M_product->get_categories(),
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_produk_tambah', $data);
        $this->load->view('admin/v_footer', $data);
    }

    public function produk_tambah_aksi() {
        $this->form_validation->set_rules('name', 'Nama Produk', 'required');
        $this->form_validation->set_rules('price', 'Harga', 'required|numeric');
        $this->form_validation->set_rules('stock', 'Stok', 'required|integer');
        $this->form_validation->set_rules('category_id', 'Kategori', 'required');

        if ($this->form_validation->run() !== FALSE) {
            $name   = $this->input->post('name', TRUE);
            $slug   = strtolower(url_title($name));
            $image  = 'default.jpg';

            if (!empty($_FILES['image']['name'])) {
                $config = ['upload_path' => './uploads/products/', 'allowed_types' => 'jpg|jpeg|png|webp', 'max_size' => 2048, 'file_name' => 'produk_' . time()];
                $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    $image = $this->upload->data()['file_name'];
                } else {
                    $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                    redirect('admin/produk/tambah');
                }
            }

            $product_id = $this->M_product->insert([
                'category_id' => $this->input->post('category_id'),
                'name'        => $name,
                'slug'        => $slug,
                'description' => $this->input->post('description', TRUE),
                'price'       => $this->input->post('price'),
                'stock'       => $this->input->post('stock'),
                'image'       => $image,
                'is_custom'   => $this->input->post('is_custom') ? 1 : 0,
                'variant_name1' => $this->_variant_name('variant_name1'),
                'variant_name2' => $this->_variant_name('variant_name2'),
            ]);

            // Simpan varian warna/ukuran (Revisi #2)
            $this->M_product->save_variants(
                $product_id,
                (array)$this->input->post('variant_color'),
                (array)$this->input->post('variant_size'),
                (array)$this->input->post('variant_stock'),
                (array)$this->input->post('variant_price_delta')
            );

            $this->session->set_flashdata('success', 'Produk berhasil ditambahkan.');
            redirect('admin/produk');
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/produk/tambah');
        }
    }

    public function produk_edit($id) {
        $product = $this->M_product->get_by_id($id);
        if (!$product) { redirect('admin/produk'); }
        $data = [
            'title'      => 'Edit Produk',
            'product'    => $product,
            'variants'   => $this->M_product->get_variants($id),
            'categories' => $this->M_product->get_categories(),
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_produk_edit', $data);
        $this->load->view('admin/v_footer', $data);
    }

    public function produk_update() {
        $id   = (int)$this->input->post('id');
        $this->form_validation->set_rules('name', 'Nama Produk', 'required');
        $this->form_validation->set_rules('price', 'Harga', 'required|numeric');
        $this->form_validation->set_rules('stock', 'Stok', 'required|integer');

        if ($this->form_validation->run() !== FALSE) {
            $name = $this->input->post('name', TRUE);
            $data_update = [
                'category_id' => $this->input->post('category_id'),
                'name'        => $name,
                'slug'        => strtolower(url_title($name)),
                'description' => $this->input->post('description', TRUE),
                'price'       => $this->input->post('price'),
                'stock'       => $this->input->post('stock'),
                'is_custom'   => $this->input->post('is_custom') ? 1 : 0,
                'variant_name1' => $this->_variant_name('variant_name1'),
                'variant_name2' => $this->_variant_name('variant_name2'),
            ];

            if (!empty($_FILES['image']['name'])) {
                $config = ['upload_path' => './uploads/products/', 'allowed_types' => 'jpg|jpeg|png|webp', 'max_size' => 2048, 'file_name' => 'produk_' . $id . '_' . time()];
                $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    $data_update['image'] = $this->upload->data()['file_name'];
                } else {
                    // Sama seperti tambah: jangan diam-diam abaikan kegagalan upload
                    $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                    redirect('admin/produk/edit/' . $id);
                }
            }

            $this->M_product->update($id, $data_update);

            // Simpan ulang varian warna/ukuran (Revisi #2)
            $this->M_product->save_variants(
                $id,
                (array)$this->input->post('variant_color'),
                (array)$this->input->post('variant_size'),
                (array)$this->input->post('variant_stock'),
                (array)$this->input->post('variant_price_delta')
            );

            $this->session->set_flashdata('success', 'Produk berhasil diperbarui.');
            redirect('admin/produk');
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/produk/edit/' . $id);
        }
    }

    /**
     * Nama variasi ala Shopee — fallback ke default bila kosong (maks 50 char)
     */
    private function _variant_name($field) {
        $name = trim((string) $this->input->post($field, TRUE));
        return mb_substr($name, 0, 50);
    }

    public function produk_hapus($id) {
        $product = $this->M_product->get_by_id($id);
        if ($product && $product->image && $product->image !== 'default.jpg') {
            @unlink('./uploads/products/' . $product->image);
        }
        $this->M_product->delete($id);
        $this->session->set_flashdata('success', 'Produk berhasil dihapus.');
        redirect('admin/produk');
    }

    // -------------------------------------------------------
    // MANAJEMEN PESANAN
    // -------------------------------------------------------
    public function pesanan($offset = 0) {
        $filter = $this->input->get('status');
        $per_page = 10;
        $this->load->library('pagination');

        $config['base_url']   = base_url('admin/pesanan/');
        if ($filter) {
            $config['base_url'] .= '?status=' . $filter;
            $config['total_rows'] = $this->M_order->count_by_status($filter);
            $config['enable_query_strings'] = TRUE;
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'per_page';
        } else {
            $config['total_rows'] = $this->M_order->count_all();
            $config['uri_segment'] = 3;
        }

        $config['per_page']   = $per_page;
        
        // Styling (Same as produk)
        $config['full_tag_open']  = '<nav><ul class="pagination pagination-sm justify-content-center gap-2 border-0">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['num_tag_open']   = '<li class="page-item">';
        $config['num_tag_close']  = '</li>';
        $config['cur_tag_open']   = '<li class="page-item active"><span class="page-link bg-admin-primary border-0 rounded-3">';
        $config['cur_tag_close']  = '</span></li>';
        $config['next_tag_open']  = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open']  = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['attributes']     = array('class' => 'page-link bg-admin-card border-secondary border-opacity-20 text-white rounded-3');

        $this->pagination->initialize($config);
        
        $current_offset = $filter ? (int)$this->input->get('per_page') : (int)$offset;

        $data = [
            'title'      => 'Manajemen Pesanan',
            'orders'     => $this->M_order->get_all_orders($filter ?: null, $per_page, $current_offset),
            'top_selling'=> $this->M_order->get_top_selling_products(5),
            'filter'     => $filter,
            'active_tab' => 'pesanan',
            'pagination' => $this->pagination->create_links(),
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_pesanan', $data);
        $this->load->view('admin/v_footer', $data);
    }

    public function pesanan_detail($id) {
        $order = $this->M_order->get_order_detail($id);
        if (!$order) { redirect('admin/pesanan'); }
        $data = [
            'title' => 'Detail Pesanan #' . $id,
            'order' => $order,
            'items' => $this->M_order->get_order_items($id),
            'tracking' => $this->M_order->get_tracking($id),
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_pesanan_detail', $data);
        $this->load->view('admin/v_footer', $data);
    }

    /**
     * Verifikasi / Tolak Pembayaran / Update status + tracking (Revisi #5)
     */
    public function verify_payment($order_id) {
        $order = $this->M_order->get_order_detail($order_id);
        if (!$order) { redirect('admin/pesanan'); }

        $status    = $this->input->post('status');
        $keterangan = $this->input->post('keterangan', TRUE);
        $resi      = $this->input->post('resi', TRUE);
        $courier   = $this->input->post('courier', TRUE);
        $allowed   = ['paid', 'rejected', 'processed', 'shipped', 'cancelled'];

        if (!in_array($status, $allowed)) {
            redirect('admin/pesanan');
        }

        // Resi & kurir wajib saat barang dikirim
        if ($status === 'shipped' && (empty($resi) || empty($courier))) {
            $this->session->set_flashdata('error', 'Nomor resi dan nama kurir wajib diisi saat mengirim barang.');
            redirect('admin/pesanan/detail/' . $order_id);
        }

        // Kembalikan stok saat pesanan ditolak/dibatalkan
        // (hanya pada transisi pertama, agar tidak dobel jika aksi diulang)
        $void_statuses = ['rejected', 'cancelled'];
        if (in_array($status, $void_statuses) && !in_array($order->status, $void_statuses)) {
            $this->M_order->restore_order_stock($order_id);
        }

        $label = [
            'paid'      => 'Pembayaran diverifikasi',
            'rejected'  => 'Pembayaran ditolak',
            'processed' => 'Pesanan sedang diproses',
            'shipped'   => 'Pesanan telah dikirim',
            'cancelled' => 'Pesanan dibatalkan',
        ][$status];

        $this->M_order->update_status($order_id, $status, $keterangan ?: $label, $resi, $courier);
        $this->session->set_flashdata('success', 'Status pesanan #' . $order_id . ' berhasil diperbarui.');
        redirect('admin/pesanan/detail/' . $order_id);
    }

    // -------------------------------------------------------
    // CHAT CUSTOMER (Revisi #7)
    // -------------------------------------------------------
    public function chat() {
        $data = [
            'title'      => 'Chat Pelanggan',
            'inbox'      => $this->M_chat->get_inbox(),
            'conv'       => null,
            'active_tab' => 'chat',
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_chat', $data);
        $this->load->view('admin/v_footer', $data);
    }

    public function chat_thread($conversation_id) {
        $conv = $this->M_chat->get_conversation($conversation_id);
        if (!$conv) { redirect('admin/chat'); }
        $this->M_chat->mark_read($conversation_id, 'admin');

        $data = [
            'title'   => 'Chat: ' . ($conv->username ?: 'User #' . $conv->user_id),
            'inbox'   => $this->M_chat->get_inbox(),
            'conv'    => $conv,
            'history' => $this->M_chat->get_history($conversation_id),
            'chat_products' => $this->M_product->get_chat_list(),
            'active_tab' => 'chat',
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_chat', $data);
        $this->load->view('admin/v_footer', $data);
    }

    // -------------------------------------------------------
    // MANAJEMEN USER
    // -------------------------------------------------------
    public function users($offset = 0) {
        $per_page = 10;
        $this->load->library('pagination');

        $config['base_url']   = base_url('admin/users/');
        $config['total_rows'] = $this->M_user->count_users();
        $config['per_page']   = $per_page;
        $config['uri_segment'] = 3;

        // Styling
        $config['full_tag_open']  = '<nav><ul class="pagination pagination-sm justify-content-center gap-2 border-0">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['num_tag_open']   = '<li class="page-item">';
        $config['num_tag_close']  = '</li>';
        $config['cur_tag_open']   = '<li class="page-item active"><span class="page-link bg-admin-primary border-0 rounded-3">';
        $config['cur_tag_close']  = '</span></li>';
        $config['next_tag_open']  = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open']  = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['attributes']     = array('class' => 'page-link bg-admin-card border-secondary border-opacity-20 text-white rounded-3');

        $this->pagination->initialize($config);

        $data = [
            'title'      => 'Manajemen Pengguna',
            'users'      => $this->M_user->get_all($per_page, $offset),
            'active_tab' => 'users',
            'pagination' => $this->pagination->create_links(),
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_users', $data);
        $this->load->view('admin/v_footer', $data);
    }

    // -------------------------------------------------------
    // MANAJEMEN KATEGORI
    // -------------------------------------------------------
    public function kategori() {
        $data = [
            'title'      => 'Manajemen Kategori',
            'categories' => $this->M_product->get_categories(),
            'active_tab' => 'kategori',
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_kategori', $data);
        $this->load->view('admin/v_footer', $data);
    }

    public function kategori_tambah() {
        $this->form_validation->set_rules('name', 'Nama Kategori', 'required|is_unique[categories.name]');

        if ($this->form_validation->run() !== FALSE) {
            $name = $this->input->post('name', TRUE);
            $this->M_product->insert_category([
                'name' => $name,
                'slug' => strtolower(url_title($name))
            ]);
            $this->session->set_flashdata('success', 'Kategori berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', validation_errors());
        }
        redirect('admin/kategori');
    }

    public function kategori_update() {
        $id   = $this->input->post('id');
        $name = $this->input->post('name', TRUE);
        $this->form_validation->set_rules('name', 'Nama Kategori', 'required');

        if ($this->form_validation->run() !== FALSE) {
            // Cegah duplikat nama kategori (kecuali milik kategori ini sendiri)
            $dup = $this->db->where('name', $name)->where('id !=', (int)$id)->count_all_results('categories');
            if ($dup) {
                $this->session->set_flashdata('error', 'Nama kategori sudah dipakai.');
                redirect('admin/kategori');
            }
            $this->M_product->update_category($id, [
                'name' => $name,
                'slug' => strtolower(url_title($name))
            ]);
            $this->session->set_flashdata('success', 'Kategori berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', validation_errors());
        }
        redirect('admin/kategori');
    }

    public function kategori_hapus($id) {
        // Blokir penghapusan bila masih ada produk di dalamnya
        // (mencegah produk yatim yang hilang dari halaman kategori)
        $used = $this->db->where('category_id', (int)$id)->count_all_results('products');
        if ($used) {
            $this->session->set_flashdata('error', "Kategori tidak dapat dihapus karena masih dimiliki {$used} produk.");
            redirect('admin/kategori');
        }
        $this->M_product->delete_category($id);
        $this->session->set_flashdata('success', 'Kategori berhasil dihapus.');
        redirect('admin/kategori');
    }

    // -------------------------------------------------------
    // VISUALISASI REKOMENDASI AI
    // -------------------------------------------------------
    public function rekomendasi() {
        $stats = $this->_get_ai_stats();
        
        // Ambil daftar user untuk dropdown simulasi
        $this->db->select('id, username');
        $this->db->from('users');
        $this->db->where('role', 'user');
        $this->db->order_by('id', 'ASC');
        $this->db->limit(20);
        $user_list = $this->db->get()->result();
        
        $data = [
            'title'      => 'Visualisasi AI Collaborative Filtering',
            'active_tab' => 'rekomendasi',
            'stats'      => $stats,
            'user_list'  => $user_list
        ];

        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_rekomendasi', $data);
        $this->load->view('admin/v_footer', $data);
    }

    // -------------------------------------------------------
    // PENGATURAN — edit .env dari browser (tanpa kirim file)
    // -------------------------------------------------------
    public function pengaturan() {
        $this->load->library('env');

        $data = [
            'title'        => 'Pengaturan — JiDoor Store',
            'active_tab'   => 'pengaturan',
            'env'          => $this->env->read(),
            'env_path'     => $this->env->path(),
            'env_writable' => $this->env->is_writable(),
            'sleeves'      => $this->M_product->get_sleeves(),
            'materials'    => $this->M_product->get_materials(),
        ];
        $this->load->view('admin/v_header', $data);
        $this->load->view('admin/v_pengaturan', $data);
        $this->load->view('admin/v_footer', $data);
    }

    // -------------------------------------------------------
    // PENGATURAN — kelola pilihan lengan & bahan kustom
    // -------------------------------------------------------
    public function lengan_simpan() {
        $id    = (int)$this->input->post('id');
        $name  = trim((string)$this->input->post('name', TRUE));
        $delta = (float)$this->input->post('price_delta');

        if ($name === '') {
            $this->session->set_flashdata('error', 'Nama jenis lengan wajib diisi.');
            redirect('admin/pengaturan');
        }

        $data = [
            'name'        => mb_substr($name, 0, 50),
            'price_delta' => $delta,
            'is_active'   => $this->input->post('is_active') ? 1 : 0,
        ];
        $this->M_product->save_sleeve($data, $id > 0 ? $id : null);
        $this->session->set_flashdata('success', 'Jenis lengan tersimpan.');
        redirect('admin/pengaturan');
    }

    public function lengan_hapus($id) {
        $this->M_product->delete_sleeve((int)$id);
        $this->session->set_flashdata('success', 'Jenis lengan dihapus.');
        redirect('admin/pengaturan');
    }

    public function bahan_simpan() {
        $id     = (int)$this->input->post('id');
        $name   = trim((string)$this->input->post('name', TRUE));
        $fabric = (float)$this->input->post('fabric_price');
        $sablon = (float)$this->input->post('sablon_price');

        if ($name === '') {
            $this->session->set_flashdata('error', 'Nama bahan wajib diisi.');
            redirect('admin/pengaturan');
        }

        $data = [
            'name'         => mb_substr($name, 0, 100),
            'fabric_price' => $fabric,
            'sablon_price' => $sablon,
            'is_active'    => $this->input->post('is_active') ? 1 : 0,
        ];
        $this->M_product->save_material($data, $id > 0 ? $id : null);
        $this->session->set_flashdata('success', 'Bahan kustom tersimpan.');
        redirect('admin/pengaturan');
    }

    public function bahan_hapus($id) {
        $this->M_product->delete_material((int)$id);
        $this->session->set_flashdata('success', 'Bahan kustom dihapus.');
        redirect('admin/pengaturan');
    }

    public function pengaturan_simpan() {
        $this->load->library('env');

        $db_host     = trim((string)$this->input->post('db_host'));
        $db_port     = trim((string)$this->input->post('db_port'));
        $db_user     = trim((string)$this->input->post('db_user'));
        $db_password = (string)$this->input->post('db_password');
        $db_name     = trim((string)$this->input->post('db_name'));
        $mid_server  = trim((string)$this->input->post('midtrans_server_key'));
        $mid_client  = trim((string)$this->input->post('midtrans_client_key'));
        $mid_prod    = $this->input->post('midtrans_production') ? 'true' : 'false';
        $api_base    = trim((string)$this->input->post('api_base_url'));

        if ($db_host === '' || $db_port === '' || $db_user === '' || $db_name === '') {
            $this->session->set_flashdata('error', 'Host, port, user, dan nama database wajib diisi.');
            redirect('admin/pengaturan');
            return;
        }

        // Tes koneksi DB dengan nilai baru — antisipasi lockout (salah port/password)
        $test = @mysqli_connect($db_host, $db_user, $db_password, $db_name, (int)$db_port);
        if (!$test) {
            $this->session->set_flashdata('error', 'Koneksi database GAGAL dengan nilai tersebut — perubahan dibatalkan. Periksa host/port/user/password/nama DB.');
            redirect('admin/pengaturan');
            return;
        }
        mysqli_close($test);

        if ($api_base === '') {
            $api_base = 'http://127.0.0.1:8000';
        }

        $updates = [
            'DB_HOST'                => $db_host,
            'DB_PORT'                => $db_port,
            'DB_USER'                => $db_user,
            'DB_PASSWORD'            => $db_password,
            'DB_NAME'                => $db_name,
            'MIDTRANS_SERVER_KEY'    => $mid_server,
            'MIDTRANS_CLIENT_KEY'    => $mid_client,
            'MIDTRANS_IS_PRODUCTION' => $mid_prod,
            'PY_API_BASE_URL'        => $api_base,
        ];

        if ($this->env->write($updates)) {
            $this->session->set_flashdata('success', 'Pengaturan tersimpan. Perubahan berlaku pada permintaan berikutnya.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menulis file .env. Pastikan folder proyek dapat ditulis oleh server web.');
        }
        redirect('admin/pengaturan');
    }

    private function _get_ai_stats() {
        $url = PY_API_BASE_URL . "/admin/stats";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            return json_decode($response);
        }
        return null;
    }
}
