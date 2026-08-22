<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Konfigurasi Midtrans Snap (Revisi #6)
 *
 * Kunci TIDAK lagi disimpan di file ini — dibaca dari environment
 * yang dimuat index.php dari file .env di root proyek.
 * Lihat .env.example untuk formatnya:
 *
 *   MIDTRANS_SERVER_KEY     = SB-MidServer-xxxx
 *   MIDTRANS_CLIENT_KEY     = SB-MidClient-xxxx
 *   MIDTRANS_IS_PRODUCTION  = false
 */

if (getenv('MIDTRANS_SERVER_KEY') === false || getenv('MIDTRANS_CLIENT_KEY') === false) {
	// .env belum ada / belum diisi — beri pesan jelas, bukan error samar 401 Midtrans
	show_error('Kunci Midtrans belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY & MIDTRANS_CLIENT_KEY pada file .env (lihat .env.example).', 500, 'Konfigurasi Pembayaran Belum Lengkap');
}

define('MIDTRANS_SERVER_KEY', getenv('MIDTRANS_SERVER_KEY'));
define('MIDTRANS_CLIENT_KEY', getenv('MIDTRANS_CLIENT_KEY'));
define('MIDTRANS_IS_PRODUCTION', in_array(strtolower((string) getenv('MIDTRANS_IS_PRODUCTION')), ['1', 'true', 'yes'], TRUE));
