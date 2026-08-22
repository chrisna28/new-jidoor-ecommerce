<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller: Chat
 * Chat customer <-> admin (Revisi #7).
 * WebSocket untuk pesan real-time; HTTP untuk riwayat & fallback offline.
 */
class Chat extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model(['M_chat', 'M_product']);
        if (!$this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Silakan login terlebih dahulu.');
            redirect('login');
        }
    }

    /**
     * Halaman chat penuh (opsional; widget mengambang juga tersedia di semua halaman)
     */
    public function index() {
        $user_id = $this->session->userdata('user_id');
        $conv    = $this->M_chat->get_or_create_conversation($user_id);
        $this->M_chat->mark_read($conv->id, 'user');

        $data = [
            'title'      => 'Chat dengan Admin — JiDoor Store',
            'conv'       => $conv,
            'cart_count' => 0,
            'categories' => $this->M_product->get_categories(),
        ];
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_chat', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    /**
     * AJAX: muat riwayat pesan saat panel dibuka.
     * Pesan berkonteks produk membawa objek product untuk kartu chat ala Shopee.
     */
    public function history() {
        $user_id = $this->session->userdata('user_id');
        $conv    = $this->M_chat->get_or_create_conversation($user_id);
        $this->M_chat->mark_read($conv->id, 'user');

        $messages = [];
        foreach ($this->M_chat->get_history($conv->id) as $m) {
            $messages[] = [
                'role'    => $m->sender_role,
                'text'    => $m->message,
                'sent_at' => date('H:i', strtotime($m->created_at)),
                'product' => $m->product_id ? [
                    'id'    => (int)$m->product_id,
                    'name'  => $m->product_name,
                    'slug'  => $m->product_slug,
                    'price' => (float)$m->product_price,
                    'image' => $m->product_image,
                ] : null,
            ];
        }
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['conversation_id' => (int)$conv->id, 'messages' => $messages]));
    }

    /**
     * Fallback HTTP: kirim pesan saat daemon mati / admin offline.
     * Pesan tetap tersimpan; admin melihatnya di inbox.
     * Menerima product_id opsional (konteks produk ala Shopee).
     */
    public function offline_message() {
        $user_id = $this->session->userdata('user_id');
        $text    = trim((string)$this->input->post('message'));
        if ($text === '' || mb_strlen($text) > 1000) {
            $this->output->set_status_header(400)
                         ->set_content_type('application/json')
                         ->set_output(json_encode(['error' => 'Pesan tidak valid.']));
            return;
        }

        // Validasi konteks produk: hanya simpan jika produknya benar-benar ada
        $product_id = (int)$this->input->post('product_id');
        if ($product_id && !$this->M_product->get_by_id($product_id)) {
            $product_id = 0;
        }

        $conv = $this->M_chat->get_or_create_conversation($user_id);
        $this->M_chat->send_message($conv->id, $user_id, 'user', $text, $product_id ?: NULL);

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['ok' => TRUE]));
    }
}
