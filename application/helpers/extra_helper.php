<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Fungsi untuk menghasilkan input hidden CSRF secara otomatis (ala CI4/Laravel)
 */
if ( ! function_exists('csrf_field')) {
    function csrf_field() {
        $ci =& get_instance();
        $name = $ci->security->get_csrf_token_name();
        $hash = $ci->security->get_csrf_hash();
        return '<input type="hidden" name="' . $name . '" value="' . $hash . '" style="display:none;">';
    }
}

/**
 * Label Bahasa Indonesia untuk nilai status order di database.
 * Nilai DB tidak diubah karena dipakai sebagai logic key.
 */
if ( ! function_exists('status_label_id')) {
    function status_label_id($status) {
        $map = [
            'pending'   => 'Menunggu Pembayaran',
            'paid'      => 'Terbayar',
            'processed' => 'Diproses',
            'shipped'   => 'Dikirim',
            'delivered' => 'Diterima',
            'rejected'  => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];
        $status = strtolower((string) $status);
        return isset($map[$status]) ? $map[$status] : ucwords($status);
    }
}

/**
 * Label Bahasa Indonesia untuk badge produk. Nilai origin dari API
 * rekomendasi tetap bahasa Inggris — diterjemahkan saat render.
 */
if ( ! function_exists('badge_label_id')) {
    function badge_label_id($text) {
        static $map = [
            'BEST MATCH'    => 'Paling Cocok',
            'FOR YOU'       => 'Untuk Anda',
            'STYLE MATCH'   => 'Sesuai Gaya',
            'HOT HITS'      => 'Terpopuler',
            'TRENDING'      => 'Sedang Tren',
            'BEST SELLER'   => 'Terlaris',
            'NEW ARRIVAL'   => 'Produk Baru',
            'SIMILAR'       => 'Serupa',
        ];
        $key = strtoupper(trim((string) $text));
        return isset($map[$key]) ? $map[$key] : $text;
    }
}

/**
 * Tanggal berbahasa Indonesia, format: Sen, 24 Agu 2026.
 */
if ( ! function_exists('tanggal_indo')) {
    function tanggal_indo($time = NULL) {
        $ts    = $time ? strtotime($time) : time();
        $hari  = ['Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];
        $bulan = ['Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr', 'May' => 'Mei', 'Jun' => 'Jun',
                  'Jul' => 'Jul', 'Aug' => 'Agu', 'Sep' => 'Sep', 'Oct' => 'Okt', 'Nov' => 'Nov', 'Dec' => 'Des'];
        return strtr(date('D', $ts), $hari) . ', ' . date('d', $ts) . ' ' . strtr(date('M', $ts), $bulan) . ' ' . date('Y', $ts);
    }
}
