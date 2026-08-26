<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

// Konfigurasi DB dibaca dari .env (getenv). Fallback ke default MAMP untuk
// pengembangan lokal. Catatan: cek `=== false` (bukan `?:`) untuk password
// agar password KOSONG (XAMPP default) tidak salah diganti ke 'root'.
$db_host     = getenv('DB_HOST');
$db_port     = getenv('DB_PORT');
$db_user     = getenv('DB_USER');
$db_password = getenv('DB_PASSWORD');
$db_name     = getenv('DB_NAME');

if ($db_host === false)     { $db_host = '127.0.0.1'; }
if ($db_port === false)     { $db_port = 8889; }
if ($db_user === false)     { $db_user = 'root'; }
if ($db_password === false) { $db_password = 'root'; } // bila .env TIDAK ada
if ($db_name === false)     { $db_name = 'ecommerce_db'; }

$db['default'] = array(
	'dsn'	     => '',
	'hostname'   => $db_host,
	'port'       => $db_port,
	'username'   => $db_user,
	'password'   => $db_password,
	'database'   => $db_name,
	'dbdriver'   => 'mysqli',
	'dbprefix'   => '',
	'pconnect'   => FALSE,
	'db_debug'   => (ENVIRONMENT !== 'production'),
	'cache_on'   => FALSE,
	'cachedir'   => '',
	'char_set'   => 'utf8mb4',
	'dbcollat'   => 'utf8mb4_unicode_ci',
	'swap_pre'   => '',
	'encrypt'    => FALSE,
	'compress'   => FALSE,
	'stricton'   => FALSE,
	'failover'   => array(),
	'save_queries' => TRUE
);
