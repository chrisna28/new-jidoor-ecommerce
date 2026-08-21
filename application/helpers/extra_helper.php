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
