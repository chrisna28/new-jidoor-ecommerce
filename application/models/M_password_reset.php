<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model: M_password_reset
 * Mengelola token reset password (berbatas waktu, sekali pakai)
 */
class M_password_reset extends CI_Model {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
    }

    /**
     * Simpan token reset baru. Token lama milik email yang sama
     * ditandai terpakai agar hanya tautan terbaru yang valid.
     */
    public function create($email, $token_hash, $expires_at) {
        $this->db->where('email', $email)
                 ->where('used', 0)
                 ->update('password_resets', ['used' => 1]);

        return $this->db->insert('password_resets', [
            'email'      => $email,
            'token_hash' => $token_hash,
            'expires_at' => $expires_at,
        ]);
    }

    /**
     * Cari token valid: cocok, belum dipakai, belum kedaluwarsa
     */
    public function find_valid($token_hash) {
        return $this->db->where('token_hash', $token_hash)
                        ->where('used', 0)
                        ->where('expires_at >', date('Y-m-d H:i:s'))
                        ->get('password_resets')->row();
    }

    /**
     * Tandai token sudah digunakan
     */
    public function mark_used($id) {
        $this->db->where('id', $id);
        return $this->db->update('password_resets', ['used' => 1]);
    }
}
