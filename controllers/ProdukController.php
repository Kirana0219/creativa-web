<?php

require_once 'models/ProdukModel.php';

class ProdukController {
    private $produkModel;

    public function __construct() {
        $this->produkModel = new ProdukModel();
    }

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=signin');
            exit;
        }
    }

    // List products
    public function index() {
        $this->checkAuth();

        // Get filter inputs
        $search = $_GET['search'] ?? '';
        $categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
        $status = $_GET['status'] ?? ''; // 'in_stock', 'low_stock', 'out_of_stock'
        $allowedStatuses = ['', 'in_stock', 'low_stock', 'out_of_stock'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        // Sorting
        $sort = $_GET['sort'] ?? 'newest';
        $allowedSorts = ['newest', 'oldest', 'price_asc', 'price_desc'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'newest';
        }

        // Pagination
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $allowedLimits = [5, 10, 20, 50];
        if (!in_array($limit, $allowedLimits, true)) {
            $limit = 10;
        }
        $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
        if ($page < 1) $page = 1;

        $filters = [
            'search' => $search,
            'category_id' => $categoryId ?: '',
            'status' => $status
        ];

        // Fetch data
        $totalProductsFiltered = $this->produkModel->countProducts($filters);
        $totalPages = (int) ceil($totalProductsFiltered / $limit);
        if ($totalPages < 1) $totalPages = 1;
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $limit;
        $products = $this->produkModel->getAllProducts($filters, $sort, $limit, $offset);

        // Categories list for filter dropdown
        $categories = $this->produkModel->getAllCategories();

        // Statistics cards data
        $stats = $this->produkModel->getSummaryStats();

        // Pass variables to view
        $title = "Product Catalog";
        require 'views/products/index.php';
    }

    // Show create form
    public function create() {
        $this->checkAuth();

        $categories = $this->produkModel->getAllCategories();
        $title = "Add Product";
        require 'views/products/create.php';
    }

    // Handle store action
    public function store() {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=products');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $categoryId = $_POST['category_id'] ?? null;
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;

        if (empty($name)) {
            $_SESSION['error'] = 'Nama produk wajib diisi!';
            header('Location: index.php?page=products');
            exit;
        }

        // Handle Image Upload
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
                $uploadFileDir = './assets/uploads/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $imageName = $newFileName;
                }
            }
        }

        $data = [
            'name' => $name,
            'category_id' => $categoryId,
            'price' => $price,
            'stock' => $stock,
            'image' => $imageName
        ];

        if ($this->produkModel->createProduct($data)) {
            $_SESSION['success'] = 'Produk berhasil ditambahkan!';
        } else {
            $_SESSION['error'] = 'Gagal menambahkan produk!';
        }

        header('Location: index.php?page=products');
        exit;
    }

    // Show edit form
    public function edit() {
        $this->checkAuth();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?page=products');
            exit;
        }

        $product = $this->produkModel->getProductById($id);
        if (!$product) {
            $_SESSION['error'] = 'Produk tidak ditemukan!';
            header('Location: index.php?page=products');
            exit;
        }

        $categories = $this->produkModel->getAllCategories();
        $title = "Edit Product";
        require 'views/products/edit.php';
    }

    // Handle update action
    public function update() {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=products');
            exit;
        }

        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $categoryId = $_POST['category_id'] ?? null;
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;

        if (!$id || empty($name)) {
            $_SESSION['error'] = 'ID dan Nama produk wajib diisi!';
            header('Location: index.php?page=products');
            exit;
        }

        $product = $this->produkModel->getProductById($id);
        if (!$product) {
            $_SESSION['error'] = 'Produk tidak ditemukan!';
            header('Location: index.php?page=products');
            exit;
        }

        $imageName = $product['image'];

        // Handle Image Upload if any
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
                $uploadFileDir = './assets/uploads/';
                
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Delete old image if it exists
                    if ($product['image'] && file_exists($uploadFileDir . $product['image'])) {
                        unlink($uploadFileDir . $product['image']);
                    }
                    $imageName = $newFileName;
                }
            }
        }

        $data = [
            'name' => $name,
            'category_id' => $categoryId,
            'price' => $price,
            'stock' => $stock,
            'image' => $imageName
        ];

        if ($this->produkModel->updateProduct($id, $data)) {
            $_SESSION['success'] = 'Produk berhasil diubah!';
        } else {
            $_SESSION['error'] = 'Gagal mengubah produk!';
        }

        header('Location: index.php?page=products');
        exit;
    }

    // Handle delete action
    public function delete() {
        $this->checkAuth();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?page=products');
            exit;
        }

        $product = $this->produkModel->getProductById($id);
        if ($product) {
            // Delete image file
            if ($product['image']) {
                $filePath = './assets/uploads/' . $product['image'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            if ($this->produkModel->deleteProduct($id)) {
                $_SESSION['success'] = 'Produk berhasil dihapus!';
            } else {
                $_SESSION['error'] = 'Gagal menghapus produk!';
            }
        }

        header('Location: index.php?page=products');
        exit;
    }

    // Quick helper to add category
    public function addCategory() {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            if (!empty($name)) {
                $this->produkModel->createCategory($name);
                $_SESSION['success'] = 'Kategori berhasil ditambahkan!';
            }
        }
        header('Location: index.php?page=products&action=create');
        exit;
    }
}
