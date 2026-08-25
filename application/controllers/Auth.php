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
        $this->load->model('M_user');
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
        $data['title'] = 'Masuk — JiDoor Store';
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
    // LUPA PASSWORD (sederhana — tanpa email/token)
    // -------------------------------------------------------

    /**
     * Halaman & proses atur ulang password langsung:
     * masukkan email terdaftar + password baru.
     */
    public function lupa_password() {
        if ($this->input->post()) {
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('password', 'Password Baru', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|matches[password]');

            if ($this->form_validation->run() !== FALSE) {
                $email = $this->input->post('email', TRUE);
                $user  = $this->M_user->get_by_email($email);

                if (!$user) {
                    $this->session->set_flashdata('error', 'Email tidak terdaftar. Periksa kembali atau daftar akun baru.');
                    redirect('lupa-password');
                }

                $this->M_user->update_password(
                    $user->id,
                    password_hash($this->input->post('password'), PASSWORD_DEFAULT)
                );

                $this->session->set_flashdata('success', 'Password berhasil diperbarui! Silakan login dengan password baru.');
                redirect('login');
            }

            $this->session->set_flashdata('error', validation_errors());
            redirect('lupa-password');
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
     * Logout
     */
    public function logout() {
        $this->session->sess_destroy();
        redirect('login');
    }
}
