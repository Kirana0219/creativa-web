<?php

require_once 'config/database.php';

class UserModel {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get user by email
    public function getUserByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get user by ID
    public function getUserById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Create new user
    public function createUser($data) {
        $query = "INSERT INTO {$this->table} 
                  (name, email, password, avatar, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        $avatar = $data['avatar'] ?? null;
        
        $stmt->bind_param(
            "ssss",
            $data['name'],
            $data['email'],
            $data['password'],
            $avatar
        );

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    // Update user
    public function updateUser($id, $data) {
        $fields = [];
        $values = [];
        $types = "";

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
            $types .= is_int($value) ? "i" : "s";
        }

        $values[] = $id;
        $types .= "i";

        $query = "UPDATE {$this->table} SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$values);

        return $stmt->execute();
    }


    // Delete user
    public function deleteUser($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Get all users
    public function getAllUsers() {
        $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Check if email exists
    public function emailExists($email) {
        $query = "SELECT id FROM {$this->table} WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
?>