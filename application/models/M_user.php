<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model: M_user
 * Mengelola data pengguna (users)
 */
class M_user extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Ambil user berdasarkan username (untuk login)
     */
    public function get_by_username($username) {
        return $this->db->get_where('users', ['username' => $username])->row();
    }

    /**
     * Ambil user berdasarkan email
     */
    public function get_by_email($email) {
        return $this->db->get_where('users', ['email' => $email])->row();
    }

    /**
     * Ambil user berdasarkan ID
     */
    public function get_by_id($id) {
        return $this->db->get_where('users', ['id' => $id])->row();
    }

    /**
     * Ambil semua pengguna
     */
    public function get_all($limit = null, $offset = 0) {
        $this->db->order_by('id', 'DESC');
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get('users')->result();
    }

    /**
     * Hitung total user
     */
    public function count_users() {
        return $this->db->count_all('users');
    }

    /**
     * Daftarkan user baru
     */
    public function register($data) {
        return $this->db->insert('users', $data);
    }

    /**
     * Update data user
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }

    /**
     * Cek apakah username sudah digunakan
     */
    public function is_username_taken($username) {
        return $this->db->get_where('users', ['username' => $username])->num_rows() > 0;
    }

    /**
     * Cek apakah email sudah digunakan
     */
    public function is_email_taken($email) {
        return $this->db->get_where('users', ['email' => $email])->num_rows() > 0;
    }

    /**
     * Update password user berdasarkan ID (sudah ter-hash)
     */
    public function update_password($user_id, $hash) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', ['password' => $hash]);
    }
}
