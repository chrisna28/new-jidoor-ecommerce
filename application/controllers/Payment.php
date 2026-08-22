<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller: Payment
 * Endpoint notifikasi (webhook) Midtrans — Revisi #6.
 *
 * URL: /payment/notification  (POST dari server Midtrans)
 * CSRF dikecualikan untuk URI ini via $config['csrf_exclude_uris'].
 */
class Payment extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_order');
    }

    private function _init_midtrans() {
        require_once APPPATH . 'config/midtrans.php';
        \Midtrans\Config::$serverKey    = MIDTRANS_SERVER_KEY;
        \Midtrans\Config::$isProduction = MIDTRANS_IS_PRODUCTION;
        \Midtrans\Config::$isSanitized  = TRUE;
        \Midtrans\Config::$is3ds        = TRUE;
    }

    /**
     * Webhook notifikasi pembayaran.
     * SELALU verifikasi ulang ke API Get Status — jangan percaya payload mentah.
     */
    public function notification() {
        $this->_init_midtrans();
        try {
            $notif = new \Midtrans\Notification();

            // Verifikasi ulang ke server Midtrans (anti-forgery)
            $transaction = \Midtrans\Transaction::status($notif->order_id);

            // Format order_id kita: JIDOOR-{order_id}-{timestamp}
            $parts    = explode('-', $transaction->order_id);
            $order_id = (int) ($parts[1] ?? 0);
            if ($order_id <= 0) {
                http_response_code(200);
                return;
            }

            if (in_array($transaction->transaction_status, ['capture', 'settlement'])) {
                $this->M_order->mark_paid_if_pending($order_id); // idempotent
            } elseif (in_array($transaction->transaction_status, ['deny', 'cancel', 'expire'])) {
                $this->M_order->mark_cancelled_if_pending($order_id); // idempotent
            }

            http_response_code(200);
            echo 'OK';
        } catch (Exception $e) {
            log_message('error', 'Midtrans notify: ' . $e->getMessage());
            http_response_code(500);
            echo 'ERROR';
        }
    }
}
