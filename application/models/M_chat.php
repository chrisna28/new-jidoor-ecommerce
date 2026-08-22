<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model: M_chat
 * Percakapan customer <-> admin (Revisi #7).
 */
class M_chat extends CI_Model {

    /**
     * Ambil percakapan user, buat baru jika belum ada (1 user = 1 conv)
     */
    public function get_or_create_conversation($user_id) {
        $conv = $this->db->get_where('conversations', ['user_id' => $user_id])->row();
        if ($conv) { return $conv; }

        $this->db->insert('conversations', ['user_id' => $user_id]);
        return $this->db->get_where('conversations', ['id' => $this->db->insert_id()])->row();
    }

    /**
     * Simpan pesan + update counter & waktu percakapan.
     * $product_id opsional: konteks produk ala Shopee (pesan tanya barang spesifik).
     * Return id pesan baru.
     */
    public function send_message($conversation_id, $sender_id, $sender_role, $message, $product_id = NULL) {
        $this->db->insert('messages', [
            'conversation_id' => $conversation_id,
            'sender_id'       => $sender_id,
            'sender_role'     => $sender_role,
            'message'         => $message,
            'product_id'      => $product_id ?: NULL,
        ]);
        $msg_id = $this->db->insert_id();

        // Penerima = lawan role pengirim
        $counter = ($sender_role === 'admin') ? 'unread_user' : 'unread_admin';
        $this->db->set($counter, $counter . '+1', FALSE)
                 ->set('last_message_at', date('Y-m-d H:i:s'))
                 ->where('id', $conversation_id)
                 ->update('conversations');

        return $msg_id;
    }

    /**
     * Riwayat pesan percakapan (urut waktu naik), maks 200 terakhir.
     * Pesan berkonteks produk membawa data produk untuk kartu chat.
     */
    public function get_history($conversation_id, $limit = 200) {
        $all = $this->db->select('m.*, p.name as product_name, p.slug as product_slug,
                                  p.price as product_price, p.image as product_image')
                        ->from('messages m')
                        ->join('products p', 'p.id = m.product_id', 'left')
                        ->where('m.conversation_id', $conversation_id)
                        ->order_by('m.created_at', 'ASC')
                        ->get()->result();
        return array_slice($all, -$limit);
    }

    /**
     * Reset counter belum-dibaca untuk sisi tertentu saat membuka thread
     */
    public function mark_read($conversation_id, $reader_role) {
        $col = ($reader_role === 'admin') ? 'unread_admin' : 'unread_user';
        $this->db->set($col, 0)->where('id', $conversation_id)->update('conversations');
    }

    /**
     * Total pesan belum dibaca admin (untuk badge sidebar)
     */
    public function count_unread_admin() {
        $row = $this->db->select_sum('unread_admin', 'total')->get('conversations')->row();
        return (int) ($row->total ?? 0);
    }

    /**
     * Inbox admin: semua percakapan terurut pesan terbaru + badge unread
     * + cuplikan pesan terakhir & penanda konteks produk
     */
    public function get_inbox() {
        return $this->db->select("c.id, c.user_id, c.last_message_at, c.unread_admin,
                                 u.username, u.email,
                                 (SELECT m.message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_text,
                                 (SELECT m.product_id FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_product_id")
                        ->from('conversations c')
                        ->join('users u', 'u.id = c.user_id', 'left')
                        ->order_by('c.last_message_at', 'DESC')
                        ->get()->result();
    }

    /**
     * Detail percakapan by id
     */
    public function get_conversation($conversation_id) {
        return $this->db->select('c.*, u.username')
                        ->from('conversations c')
                        ->join('users u', 'u.id = c.user_id', 'left')
                        ->where('c.id', $conversation_id)
                        ->get()->row();
    }
}
