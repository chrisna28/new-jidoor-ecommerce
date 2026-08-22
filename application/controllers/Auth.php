<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller: Auth
 * Mengelola Login, Register, Logout, dan Lupa Password
 */
class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model(['M_user', 'M_password_reset']);
    }

    /**
     * Halaman Login
     */
    public function index() {
        // Jika sudah login, redirect sesuai role
        if ($this->session->userdata('user_id')) {
            $role = $this->session->userdata('role');
            redirect($role === 'admin' ? 'admin' : '/');
        }
        $data['title'] = 'Login — JiDoor Store';
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_login', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    /**
     * Proses Login
     * Verifikasi password_hash modern; akun lama (MD5) otomatis
     * di-upgrade ke bcrypt saat login pertama kali.
     */
    public function login_aksi() {
        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password', TRUE);

        if (empty($username) || empty($password)) {
            $this->session->set_flashdata('error', 'Username dan password wajib diisi.');
            redirect('login');
        }

        $user = $this->M_user->get_by_username($username);

        $valid = false;
        if ($user && password_verify($password, $user->password)) {
            $valid = true;
        } elseif ($user && $user->password === md5($password)) {
            // Akun lama: upgrade diam-diam ke hash modern
            $this->M_user->update_password(
                $user->id,
                password_hash($password, PASSWORD_DEFAULT)
            );
            $valid = true;
        }

        if ($valid) {
            // Set session
            $this->session->set_userdata([
                'user_id'  => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role,
                'status'   => 'logged_in'
            ]);

            if ($user->role === 'admin') {
                redirect('admin');
            } else {
                redirect('/');
            }
        } else {
            $this->session->set_flashdata('error', 'Username atau password salah.');
            redirect('login');
        }
    }

    /**
     * Halaman Register
     */
    public function register() {
        if ($this->session->userdata('user_id')) {
            redirect('/');
        }
        $data['title'] = 'Daftar Akun — JiDoor Store';
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_register', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    /**
     * Proses Register
     */
    public function register_aksi() {
        $this->form_validation->set_rules('username', 'Username', 'required|min_length[4]|max_length[50]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|matches[password]');

        if ($this->form_validation->run() !== FALSE) {
            $username = $this->input->post('username', TRUE);
            $email    = $this->input->post('email', TRUE);
            $password = $this->input->post('password', TRUE);
            $phone    = $this->input->post('phone', TRUE);

            // Cek duplikat
            if ($this->M_user->is_username_taken($username)) {
                $this->session->set_flashdata('error', 'Username sudah digunakan.');
                redirect('register');
            }
            if ($this->M_user->is_email_taken($email)) {
                $this->session->set_flashdata('error', 'Email sudah terdaftar.');
                redirect('register');
            }

            $data = [
                'username' => $username,
                'email'    => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role'     => 'user',
                'phone'    => $phone
            ];

            $this->M_user->register($data);
            $this->session->set_flashdata('success', 'Registrasi berhasil! Silakan login.');
            redirect('login');
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect('register');
        }
    }

    // -------------------------------------------------------
    // LUPA PASSWORD
    // -------------------------------------------------------

    /**
     * Halaman & proses permintaan reset password
     */
    public function lupa_password() {
        if ($this->input->post()) {
            $email = $this->input->post('email', TRUE);
            $user  = $this->M_user->get_by_email($email);

            if ($user) {
                $token      = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);

                $this->M_password_reset->create(
                    $email,
                    $token_hash,
                    date('Y-m-d H:i:s', strtotime('+60 minutes'))
                );

                $link = site_url('reset-password/' . $token);
                $sent = $this->_send_reset_email($email, $user->username, $link);

                if (!$sent && ENVIRONMENT !== 'production') {
                    // Fallback demo offline: tampilkan tautan langsung
                    log_message('debug', 'Reset link (fallback): ' . $link);
                    $this->session->set_flashdata('success',
                        'Mode development — SMTP tidak terjangkau. Tautan reset: <a href="' .
                        $link . '" class="fw-bold">' . $link . '</a> (berlaku 60 menit)');
                    redirect('login');
                }
            }

            // Respons identik untuk email terdaftar/tidak (anti user-enumeration)
            $this->session->set_flashdata('success',
                'Jika email Anda terdaftar, tautan reset password telah dikirim. Periksa kotak masuk.');
            redirect('login');
        }

        if ($this->session->userdata('user_id')) {
            redirect('/');
        }
        $data['title'] = 'Lupa Password — JiDoor Store';
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_lupa_password', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    /**
     * Halaman & proses password baru via token
     */
    public function reset_password($token = NULL) {
        if (!$token) {
            redirect('login');
        }

        $row = $this->M_password_reset->find_valid(hash('sha256', $token));
        if (!$row) {
            $this->session->set_flashdata('error',
                'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.');
            redirect('lupa-password');
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('password', 'Password Baru', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|matches[password]');

            if ($this->form_validation->run() !== FALSE) {
                $this->M_user->update_password_by_email(
                    $row->email,
                    password_hash($this->input->post('password'), PASSWORD_DEFAULT)
                );
                $this->M_password_reset->mark_used($row->id);

                $this->session->set_flashdata('success',
                    'Password berhasil diubah! Silakan login dengan password baru.');
                redirect('login');
            }

            $this->session->set_flashdata('error', validation_errors());
            redirect('reset-password/' . $token);
        }

        $data['title']  = 'Reset Password — JiDoor Store';
        $data['token']  = $token;
        $data['masked_email'] = $this->_mask_email($row->email);
        $this->load->view('frontend/v_header', $data);
        $this->load->view('frontend/v_reset_password', $data);
        $this->load->view('frontend/v_footer', $data);
    }

    /**
     * Kirim email berisi tautan reset.
     * Return TRUE jika terkirim; FALSE jika SMTP gagal (fallback log).
     */
    private function _send_reset_email($to_email, $username, $link) {
        try {
            $config_path = APPPATH . 'config/email.php';
            if (!file_exists($config_path)) {
                return false;
            }
            include($config_path);

            $this->load->library('email', $config);
            $this->email->from($config['smtp_user'], 'JiDoor Store');
            $this->email->to($to_email);
            $this->email->subject('Reset Password — JiDoor Store');
            $this->email->message(
                '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;">' .
                '<h2 style="color:#111;">Hai ' . htmlspecialchars($username) . ',</h2>' .
                '<p>Kami menerima permintaan reset password akun JiDoor Store Anda.</p>' .
                '<p style="text-align:center;margin:32px 0;">' .
                '<a href="' . $link . '" style="background:#111;color:#fff;padding:14px 36px;' .
                'text-decoration:none;font-weight:bold;border-radius:4px;display:inline-block;">' .
                'RESET PASSWORD</a></p>' .
                '<p>Atau salin tautan berikut ke browser:<br>' .
                '<a href="' . $link . '">' . $link . '</a></p>' .
                '<p style="color:#888;font-size:12px;">Tautan berlaku 60 menit dan hanya dapat ' .
                'digunakan satu kali. Abaikan email ini jika Anda tidak merasa meminta reset.</p>' .
                '</div>'
            );

            return $this->email->send();
        } catch (Exception $e) {
            log_message('error', 'Gagal kirim email reset: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Samarkan email untuk ditampilkan: ha***@gmail.com
     */
    private function _mask_email($email) {
        $parts = explode('@', $email);
        $name  = substr($parts[0], 0, 2);
        return $name . '***@' . ($parts[1] ?? '');
    }

    /**
     * Logout
     */
    public function logout() {
        $this->session->sess_destroy();
        redirect('login');
    }
}
