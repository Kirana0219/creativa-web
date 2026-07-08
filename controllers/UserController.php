<?php

require_once 'models/UserModel.php';

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    private function checkAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=signin');
            exit;
        }
    }

    public function index()
    {
        $this->checkAuth();

        $users = $this->userModel->getAllUsers();
        $stats = $this->userModel->getSummaryStats();

        $title = "Users";
        $breadcrumb = "Dashboard > Users";

        include 'views/users/index.php';
    }

    public function store()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=users');
            exit;
        }

        $data = $this->userDataFromRequest();
        $data['avatar'] = $this->uploadAvatar(
            $data['avatar']
        );
        $success = $this->userModel->createUser($data);

        $_SESSION['user_flash'] = $success
            ? ['type' => 'success', 'message' => 'User berhasil ditambahkan.']
            : ['type' => 'danger', 'message' => 'User gagal ditambahkan.'];

        header('Location: index.php?page=users');
        exit;
    }

    public function update()
    {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=users');
            exit;
        }

        $id = (int) ($_POST['user_id'] ?? 0);
        $data = $this->userDataFromRequest();

        // Upload avatar baru jika ada
        if (
            isset($_FILES['avatar']) &&
            $_FILES['avatar']['error'] === 0
        ) {
            $data['avatar'] = $this->uploadAvatar(
                $_FILES['avatar']
            );
        } else {
            // gunakan avatar lama
            $oldUser = $this->userModel->getUserById($id);
            $data['avatar'] = $oldUser['avatar'];
        }

        $success = $id > 0 &&
            $this->userModel->updateUser($id, $data);

        if (
            $success &&
            $_SESSION['user_id'] == $id
        ) {

            $_SESSION['avatar'] = $data['avatar'];

            $_SESSION['name'] = $data['name'];

        }

        $_SESSION['user_flash'] = $success
            ? ['type' => 'success', 'message' => 'User berhasil diperbarui.']
            : ['type' => 'danger', 'message' => 'User gagal diperbarui.'];

        header('Location: index.php?page=users');
        exit;
    }

    public function delete()
    {
        $this->checkAuth();

        $id = (int) ($_GET['id'] ?? 0);

        $success = $id > 0 &&
            $this->userModel->deleteUser($id);

        $_SESSION['user_flash'] = $success
            ? ['type' => 'success', 'message' => 'User berhasil dihapus.'
            ]
            : ['type' => 'danger', 'message' => 'User gagal dihapus.'];

        header('Location: index.php?page=users');
        exit;
    }

    private function userDataFromRequest()
    {
        return [
            'name'     => trim($_POST['name'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'password' => trim($_POST['password'] ?? ''),
            'avatar'   => $_FILES['avatar'] ?? null,
            'role'     => trim($_POST['role'] ?? 'User'),
            'status'   => trim($_POST['status'] ?? 'Active')
        ];
    }

    private function uploadAvatar($file) {
    if (
        empty($file) ||
        $file['error'] !== 0
    ) {
        return null;
    }

    $folder = "assets/uploads/users/";

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $extension = pathinfo(
        $file['name'],
        PATHINFO_EXTENSION
    );

    $filename = uniqid() . "." . $extension;

    move_uploaded_file(
        $file['tmp_name'],
        $folder . $filename
    );

    return $filename;
    }
    }