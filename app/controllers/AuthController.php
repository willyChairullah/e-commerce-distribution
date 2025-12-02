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

        // Determine region_code based on mode
        if (isCentralMode()) {
            // Central mode: user pilih region dari form
            $regionCode = isset($_POST['region_code']) ? sanitize($_POST['region_code']) : '';
            
            if (empty($regionCode)) {
                $_SESSION['error'] = 'Region harus dipilih';
                redirect('/register');
                return;
            }
            
            // Validasi region code valid
            if (!array_key_exists($regionCode, AVAILABLE_REGIONS)) {
                $_SESSION['error'] = 'Region tidak valid';
                redirect('/register');
                return;
            }
        } else {
            // Regional mode: auto-set dari config server
            $regionCode = getCurrentRegion();
            
            if (empty($regionCode)) {
                $_SESSION['error'] = 'Konfigurasi region tidak valid';
                redirect('/register');
                return;
            }
        }

        $data = array(
            'full_name' => sanitize($_POST['full_name']),
            'email' => sanitize($_POST['email']),
            'password' => $_POST['password'],
            'region_code' => $regionCode
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
