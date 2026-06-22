<?php
// modules/pos/controllers/ProductController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../models/Product.php';
require_once dirname(__DIR__, 3) . '/core/helpers/url.php';

class ProductController {
    public function index() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (Tenant::getPosLevel() < 1) {
            die('Upgrade to POS Starter or higher to manage products.');
        }

        if (!Auth::isTenantAdmin()) {
            die('No permission to view products');
        }

        $products = Product::getAll();
        $categories = $this->getCategories();

        include __DIR__ . '/../views/products.php';
    }

    public function create() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            die('No permission to create products');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
        } else {
            $categories = $this->getCategories();
            include __DIR__ . '/../views/product_form.php';
        }
    }

    public function edit($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            die('No permission to edit products');
        }

        $product = Product::getById($id);
        if (!$product) {
            die('Product not found');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
        } else {
            $categories = $this->getCategories();
            include __DIR__ . '/../views/product_form.php';
        }
    }

    public function delete($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            die('No permission to delete products');
        }

        Product::delete($id);
        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/products');
        exit;
    }

    private function store() {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'price' => (float)$_POST['price'],
            'category_id' => $_POST['category_id'] ?: null,
            'stock_quantity' => (int)$_POST['stock_quantity'],
            'sku' => $_POST['sku'] ?? '',
            'barcode' => $_POST['barcode'] ?? '',
            'status' => $_POST['status'] ?? 'active'
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $data['image'] = $this->uploadImage($_FILES['image']);
        }

        Product::create($data);
        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/products');
        exit;
    }

    private function update($id) {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'price' => (float)$_POST['price'],
            'category_id' => $_POST['category_id'] ?: null,
            'stock_quantity' => (int)$_POST['stock_quantity'],
            'sku' => $_POST['sku'] ?? '',
            'barcode' => $_POST['barcode'] ?? '',
            'status' => $_POST['status'] ?? 'active'
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $data['image'] = $this->uploadImage($_FILES['image']);
        }

        Product::update($id, $data);
        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/products');
        exit;
    }

    private function getCategories() {
        $db = Database::getInstance();
        $tenantId = Tenant::getId();
        return $db->fetchAll("SELECT * FROM categories WHERE tenant_id = ? ORDER BY name", [$tenantId]);
    }

    private function uploadImage($file) {
        $uploadDir = __DIR__ . '/../../../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Increase memory limit for processing large images
        ini_set('memory_limit', '512M');

        // Generate unique filename with .webp extension
        $fileName = uniqid() . '.webp';
        $targetPath = $uploadDir . $fileName;

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            die('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
        }

        // Convert image to WebP
        $image = null;
        switch ($file['type']) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                // Preserve transparency for PNG
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file['tmp_name']);
                break;
            case 'image/webp':
                // If already WebP, just move it
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    return $fileName;
                } else {
                    die('Failed to upload image.');
                }
                break;
        }

        if ($image === null) {
            die('Failed to process image.');
        }

        // Convert and save as WebP with 80% quality
        if (imagewebp($image, $targetPath, 80)) {
            imagedestroy($image);
            return $fileName;
        } else {
            imagedestroy($image);
            die('Failed to save converted image.');
        }
    }
}
?>