<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Library: Chat_Token
 * Buat/validasi token HMAC untuk autentikasi WebSocket chat (Revisi #7).
 * Daemon memvalidasi tanpa perlu akses session CI.
 */
class Chat_Token {

    // Ganti di produksi; cukup untuk lingkup skripsi
    const SECRET = 'jidoor-chat-secret-2026';

    /**
     * Token berformat: {user_id}|{role}|{timestamp}|{hmac}
     */
    public static function make($user_id, $role) {
        $ts   = time();
        $base = $user_id . '|' . $role . '|' . $ts;
        $hmac = hash_hmac('sha256', $base, self::SECRET);
        return $base . '|' . $hmac;
    }

    /**
     * Validasi token + usia (< 24 jam).
     * Return [user_id, role] jika valid, FALSE jika tidak.
     */
    public static function check($token) {
        if (!is_string($token)) { return FALSE; }
        $parts = explode('|', $token);
        if (count($parts) !== 4) { return FALSE; }

        list($user_id, $role, $ts, $hmac) = $parts;

        if (!in_array($role, ['user', 'admin'], TRUE)) { return FALSE; }
        if (!ctype_digit((string)$user_id) || !ctype_digit((string)$ts)) { return FALSE; }
        if (abs(time() - (int)$ts) > 86400) { return FALSE; } // kedaluwarsa 24 jam

        $expected = hash_hmac('sha256', $user_id . '|' . $role . '|' . $ts, self::SECRET);
        if (!hash_equals($expected, $hmac)) { return FALSE; }

        return [(int)$user_id, $role];
    }
}
