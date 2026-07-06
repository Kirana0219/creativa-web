<?php

require_once 'models/UserModel.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // view signin
    public function signin()
    {
        $errors = isset($_SESSION['error']) ? [$_SESSION['error']] : [];
        unset($_SESSION['error']);
        require 'views/auth/signin.php';
    }

    // view signup
    public function signup()
    {
        $errors = isset($_SESSION['error']) ? [$_SESSION['error']] : [];
        unset($_SESSION['error']);
        require 'views/auth/signup.php';
    }

    // process signup
    public function signupProcess()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=signup');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $terms = $_POST['terms'] ?? false;

        // validation empty field
        if (!$name || !$email || !$password ) {
            $_SESSION['error'] = 'Semua field wajib diisi!';
            header('Location: index.php?page=signup');
            exit;
        }

        // validation password length
        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password minimal 8 karakter!';
            header('Location: index.php?page=signup');
            exit;
        }

        // validation terms
        if (!$terms) {
            $_SESSION['error'] = 'Harus menyetujui syarat & ketentuan!';
            header('Location: index.php?page=signup');
            exit;
        }

        // check email exists
        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = 'Email sudah digunakan!';
            header('Location: index.php?page=signup');
            exit;
        }

        // hash password
        $data = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ];

        // insert user
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
        $remember = $_POST['remember'] ?? false;

        // validation empty
        if (!$email || !$password) {
            $_SESSION['error'] = 'Email dan password wajib diisi!';
            header('Location: index.php?page=signin');
            exit;
        }

        // get user by email
        $user = $this->userModel->getUserByEmail($email);

        // verify password
        if ($user && password_verify($password, $user['password'])) {

            // set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];

            // remember me cookie
            if ($remember) {
                setcookie('user_email', $email, time() + (86400 * 30), '/');
            }

            header('Location: index.php?page=dashboard');
            exit;
        }

        $_SESSION['error'] = 'Email atau password salah!';
        header('Location: index.php?page=signin');
        exit;
    }

    // logout user
    public function logout()
    {
        session_start();
        session_destroy();

        header('Location: index.php?page=signin');
        exit;
    }
}