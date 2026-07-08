<?php

require_once 'config/database.php';

class UserModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getUserById($id) {
    $stmt = $this->conn->prepare("
        SELECT *
        FROM users
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc();
    }
    
    public function getAllUsers() {
    $query = "SELECT
                id,
                name,
                email,
                avatar,
                created_at,
                role,
                status,
                last_login
              FROM users
              ORDER BY created_at DESC";

    $result = $this->conn->query($query);

    return $result
        ? $result->fetch_all(MYSQLI_ASSOC)
        : [];
    }

    public function getSummaryStats() {
        $query = "
            SELECT
                COUNT(*) AS total_users,
                SUM(status = 'Active') AS active_users,
                SUM(status <> 'Active') AS inactive_users,
                SUM(role = 'Admin') AS admin_users
            FROM users
        ";

        $result = $this->conn->query($query);

        return $result
            ? $result->fetch_assoc()
            : [
                'total_users' => 0,
                'active_users' => 0,
                'inactive_users' => 0,
                'admin_users' => 0
            ];
    }

    public function createUser($data) {
        $query = "
        INSERT INTO users (
            name,
            email,
            password,
            avatar,
            status,
            role,
            created_at
        )
        VALUES (
            ?, ?, ?, ?, ?, ?, NOW()
        )";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $password = $data['password'];
        if (!str_starts_with($password, '$2y$')) {
            $password = password_hash($password, PASSWORD_DEFAULT);
        }

        $status = $data['status'] ?? 'Active';
        $role = $data['role'] ?? 'User';
        $avatar = $data['avatar'] ?? null;

        $stmt->bind_param(
            "ssssss",
            $data['name'],
            $data['email'],
            $password,
            $avatar,
            $status,
            $role
        );

    return $stmt->execute();
    }
    
    public function updateUser($id, $data) {
    if (!empty($data['password'])) {

        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        $query = "UPDATE users
                    SET
                    name=?,
                    email=?,
                    password=?,
                    role=?,
                    status=?,
                    avatar=?
                    WHERE id=?";

        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ssssssi",
            $data['name'],
            $data['email'],
            $password,
            $data['role'],
            $data['status'],
            $data['avatar'],
            $id
        );
    } else {
        $query = "UPDATE users
                    SET
                    name=?,
                    email=?,
                    role=?,
                    status=?,
                    avatar=?
                    WHERE id=?";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sssssi",
            $data['name'],
            $data['email'],
            $data['role'],
            $data['status'],
            $data['avatar'],
            $id
        );
    }



    return $stmt->execute();
    }

    public function deleteUser($id) {
    $stmt = $this->conn->prepare(
        "DELETE FROM users WHERE id=?"
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
    }

    public function getUserByEmail($email) {
    $stmt = $this->conn->prepare("
        SELECT *
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
    }
    
    public function emailExists($email) {
    $stmt = $this->conn->prepare("
        SELECT id
        FROM users
        WHERE email = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    return $stmt->get_result()->num_rows > 0;
    }

    public function updateLastLogin($id) {
    $stmt = $this->conn->prepare("
        UPDATE users
        SET last_login = NOW()
        WHERE id = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $id);

    return $stmt->execute();
    }
}
?>