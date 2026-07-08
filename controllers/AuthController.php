<?php

require_once 'models/UserModel.php';

class AuthController
{
    private $userModel;

    public function __construct(){
        $this->userModel = new UserModel();
    }

    // view signin
    public function signin(){
        $errors = isset($_SESSION['error']) ? [$_SESSION['error']] : [];
        unset($_SESSION['error']);

        require 'views/auth/signin.php';
    }

    // view signup
    public function signup(){
        $errors = isset($_SESSION['error']) ? [$_SESSION['error']] : [];
        unset($_SESSION['error']);

        require 'views/auth/signup.php';
    }

    // process signup
    public function signupProcess(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=signup');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $terms = $_POST['terms'] ?? null;

        // validation
        if (!$name || !$email || !$password) {
            $_SESSION['error'] = 'Semua field wajib diisi!';
            header('Location: index.php?page=signup');
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password minimal 8 karakter!';
            header('Location: index.php?page=signup');
            exit;
        }

        if (!$terms) {
            $_SESSION['error'] = 'Harus menyetujui syarat & ketentuan!';
            header('Location: index.php?page=signup');
            exit;
        }

        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = 'Email sudah digunakan!';
            header('Location: index.php?page=signup');
            exit;
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ];

        $result = $this->userModel->createUser($data);

        if ($result) {
            $_SESSION['success'] = 'Akun berhasil dibuat, silakan masuk!';
            header('Location: index.php?page=signin');
            exit;
        }

        $_SESSION['error'] = 'Gagal membuat akun!';
        header('Location: index.php?page=signup');
        exit;
    }

    // process signin
    public function signinProcess()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=signin');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // validation
        if (!$email || !$password) {
            $_SESSION['error'] = 'Email dan password wajib diisi!';
            header('Location: index.php?page=signin');
            exit;
        }

        // get user
        $user = $this->userModel->getUserByEmail($email);

        // check user + password
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Email atau password salah!';
            header('Location: index.php?page=signin');
            exit;
        }

        // update last login
        $this->userModel->updateLastLogin($user['id']);

        // set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];

        // remember me
        if ($remember) {
            setcookie('user_email', $email, time() + (86400 * 30), '/');
        }

        header('Location: index.php?page=dashboard');
        exit;
    }

    // logout
    public function logout()
    {
        session_destroy();
        header('Location: index.php?page=signin');
        exit;
    }
}