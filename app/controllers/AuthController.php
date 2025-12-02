<?php

/**
 * AuthController - Handle Login, Register, Logout
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $db;
    private $userModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
    }

    public function showLogin()
    {
        require_once __DIR__ . '/../../views/auth/login.php';
    }

    public function showRegister()
    {
        require_once __DIR__ . '/../../views/auth/register.php';
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
            return;
        }

        $email = sanitize($_POST['email']);
        $password = $_POST['password'];

        $user = $this->userModel->findByEmail($email);

        if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['region_code'] = $user['region_code'];

            if ($user['is_admin'] == 1) {
                redirect('/dashboard');
            } else {
                redirect('/klien');
            }
        } else {
            $_SESSION['error'] = 'Email atau password salah';
            redirect('/login');
        }
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/register');
            return;
        }

        // Auto-set region_code dari config server
        // User tidak bisa pilih region sendiri
        $regionCode = getCurrentRegion();
        
        // Validasi: Regional mode harus punya region code
        if (!isCentralMode() && empty($regionCode)) {
            $_SESSION['error'] = 'Konfigurasi region tidak valid';
            redirect('/register');
            return;
        }

        $data = array(
            'full_name' => sanitize($_POST['full_name']),
            'email' => sanitize($_POST['email']),
            'password' => $_POST['password'],
            'region_code' => $regionCode // Auto-set dari config
        );

        // Check if email already exists
        if ($this->userModel->findByEmail($data['email'])) {
            $_SESSION['error'] = 'Email sudah terdaftar';
            redirect('/register');
            return;
        }

        if ($this->userModel->create($data)) {
            $_SESSION['success'] = 'Registrasi berhasil, silakan login';
            redirect('/login');
        } else {
            $_SESSION['error'] = 'Registrasi gagal';
            redirect('/register');
        }
    }

    public function logout()
    {
        session_destroy();
        redirect('/login');
    }
}
