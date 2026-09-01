<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// -------------------------------------------------------
// DEFAULT ROUTE
// -------------------------------------------------------
$route['default_controller'] = 'welcome';
$route['404_override']       = 'welcome/notfound';
$route['translate_uri_dashes'] = FALSE;

// -------------------------------------------------------
// AUTH ROUTES
// -------------------------------------------------------
$route['login']            = 'auth/index';
$route['login/aksi']       = 'auth/login_aksi';
$route['register']         = 'auth/register';
$route['register/aksi']    = 'auth/register_aksi';
$route['logout']           = 'auth/logout';
$route['lupa-password']    = 'auth/lupa_password';

// -------------------------------------------------------
// FRONTEND ROUTES
// -------------------------------------------------------
$route['katalog']                    = 'welcome/katalog';
$route['katalog/(:num)']             = 'welcome/katalog/$1';
$route['kategori/(:any)']            = 'welcome/by_category/$1';
$route['kategori/(:any)/(:num)']     = 'welcome/by_category/$1/$2';
$route['search']                     = 'welcome/search';
$route['search/(:any)']              = 'welcome/search/$1';
$route['produk/(:any)']              = 'welcome/detail/$1';
$route['rate']                       = 'welcome/rate';
$route['riwayat-rating']             = 'welcome/rating_history';
$route['disukai']                    = 'welcome/disukai';
$route['wishlist']                   = 'welcome/disukai';

// -------------------------------------------------------
// CHAT ROUTES (Revisi #7)
// -------------------------------------------------------
$route['chat']                = 'chat/index';
$route['chat/history']        = 'chat/history';
$route['chat/offline-message'] = 'chat/offline_message';

// -------------------------------------------------------
// CART ROUTES
// -------------------------------------------------------
$route['keranjang']               = 'cart/index';
$route['keranjang/tambah']        = 'cart/add';
$route['keranjang/update']        = 'cart/update';
$route['keranjang/hapus/(:num)']  = 'cart/remove/$1';
$route['checkout']                = 'cart/checkout';
$route['checkout/proses']         = 'cart/process_checkout';
$route['checkout/bukti/(:num)']   = 'cart/upload_bukti/$1';

// -------------------------------------------------------
// ORDER ROUTES
// -------------------------------------------------------
$route['pesanan']              = 'order/riwayat';
$route['pesanan/detail/(:num)'] = 'order/detail/$1';
$route['pesanan/diterima/(:num)'] = 'order/terima/$1';
$route['pesanan/bayar/(:num)']    = 'order/bayar_midtrans/$1';
$route['pesanan/midtrans-finish/(:num)'] = 'order/midtrans_finish/$1';

// -------------------------------------------------------
// PAYMENT GATEWAY (Midtrans webhook — Revisi #6)
// -------------------------------------------------------
$route['payment/notification'] = 'payment/notification';

// -------------------------------------------------------
// ADMIN ROUTES
// -------------------------------------------------------
$route['admin']                          = 'admin/index';
$route['admin/produk']                   = 'admin/produk';
$route['admin/produk/tambah']            = 'admin/produk_tambah';
$route['admin/produk/tambah/aksi']       = 'admin/produk_tambah_aksi';
$route['admin/produk/edit/(:num)']       = 'admin/produk_edit/$1';
$route['admin/produk/update']            = 'admin/produk_update';
$route['admin/produk/hapus/(:num)']      = 'admin/produk_hapus/$1';
$route['admin/pesanan']                  = 'admin/pesanan';
$route['admin/pesanan/detail/(:num)']    = 'admin/pesanan_detail/$1';
$route['admin/pesanan/verifikasi/(:num)'] = 'admin/verify_payment/$1';
$route['admin/users']                    = 'admin/users';
$route['admin/ratings']                  = 'admin/ratings';
$route['admin/ratings/hapus/(:num)']      = 'admin/rating_hapus/$1';
$route['admin/chat']                     = 'admin/chat';
$route['admin/chat/(:num)']              = 'admin/chat_thread/$1';
$route['admin/rekomendasi']              = 'admin/rekomendasi';
$route['admin/pengaturan']               = 'admin/pengaturan';
$route['admin/pengaturan/simpan']        = 'admin/pengaturan_simpan';
$route['admin/pengaturan/lengan/simpan'] = 'admin/lengan_simpan';
$route['admin/pengaturan/lengan/hapus/(:num)'] = 'admin/lengan_hapus/$1';
$route['admin/pengaturan/bahan/simpan']  = 'admin/bahan_simpan';
$route['admin/pengaturan/bahan/hapus/(:num)']  = 'admin/bahan_hapus/$1';
