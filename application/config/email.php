<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
 | EMAIL SENDER CONFIG — Lupa Password
| -------------------------------------------------------------------
| Menggunakan SMTP Gmail. Wajib memakai App Password (bukan password
| akun biasa): https://myaccount.google.com/apppasswords
|
| FALLBACK DEMO OFFLINE: jika SMTP gagal / tanpa internet, tautan
| reset dicatat ke application/logs/reset_links.log dan (hanya saat
| ENVIRONMENT = 'development') ditampilkan langsung di layar.
*/
$config['useragent']      = 'JiDoor Store';
$config['protocol']       = 'smtp';
$config['smtp_host']      = 'ssl://smtp.gmail.com';
$config['smtp_port']      = 465;
$config['smtp_user']      = 'emailanda@gmail.com';        // TODO: ganti
$config['smtp_pass']      = 'APP-PASSWORD-16-KARAKTER';   // TODO: ganti
$config['smtp_timeout']   = 10;
$config['smtp_keepalive'] = FALSE;
$config['charset']        = 'utf-8';
$config['mailtype']       = 'html';
$config['wordwrap']       = TRUE;
$config['newline']        = "\r\n";
$config['crlf']           = "\r\n";
