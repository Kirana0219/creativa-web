<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "db_creativa";

    private $conn;

    public function getConnection() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Koneksi ke Database Gagal: " . $this->conn->connect_error);
            return null;
        }
        return $this->conn;
    }
}
?>