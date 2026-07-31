<?php
// modules/pos/controllers/IngredientController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../models/Ingredient.php';
require_once __DIR__ . '/../models/Product.php';
require_once dirname(__DIR__, 3) . '/core/helpers/url.php';

class IngredientController {
    public function index() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (Tenant::getPosLevel() < 1) {
            die('Upgrade to POS Starter or higher to manage inventory.');
        }

        if (!Auth::isTenantAdmin()) {
            die('No permission to view ingredients');
        }

        $ingredients = Ingredient::getAll();
        $logs = Ingredient::getStockLogs();

        include __DIR__ . '/../views/ingredients.php';
    }

    public function create() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name']),
                'stock_quantity' => (float)$_POST['stock_quantity'],
                'unit' => trim($_POST['unit']),
                'min_stock_alert' => (float)($_POST['min_stock_alert'] ?? 0.00)
            ];

            if ($data['name'] !== '' && $data['unit'] !== '') {
                $ingredientId = Ingredient::create($data);
                
                // Add initial stock log
                if ($data['stock_quantity'] > 0) {
                    $db = Database::getInstance();
                    $db->insert('ingredient_stock_logs', [
                        'tenant_id' => Tenant::getId(),
                        'ingredient_id' => $ingredientId,
                        'change_quantity' => $data['stock_quantity'],
                        'reason' => 'adjust'
                    ]);
                }
            }
        }

        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/ingredients');
        exit;
    }

    public function update($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name']),
                'unit' => trim($_POST['unit']),
                'min_stock_alert' => (float)($_POST['min_stock_alert'] ?? 0.00)
            ];

            Ingredient::update((int)$id, $data);
        }

        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/ingredients');
        exit;
    }

    public function topup($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $qty = (float)($_POST['quantity'] ?? 0);
            if ($qty > 0) {
                Ingredient::logTopup((int)$id, $qty);
            }
        }

        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/ingredients');
        exit;
    }

    public function delete($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        Ingredient::delete((int)$id);

        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/ingredients');
        exit;
    }

    // Ajax endpoint to save/update recipe for a specific product
    public function saveRecipe() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid method']);
            exit;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid product']);
            exit;
        }

        $recipeItems = [];
        $ingredients = $_POST['recipe_ingredients'] ?? [];
        $quantities = $_POST['recipe_quantities'] ?? [];
        $sizes = $_POST['recipe_sizes'] ?? []; // Product size ids or empty for base product

        if (is_array($ingredients) && is_array($quantities)) {
            foreach ($ingredients as $i => $ingId) {
                $ingId = (int)$ingId;
                $qty = (float)($quantities[$i] ?? 0);
                $sizeId = isset($sizes[$i]) && $sizes[$i] !== '' ? (int)$sizes[$i] : null;

                if ($ingId > 0 && $qty > 0) {
                    $recipeItems[] = [
                        'ingredient_id' => $ingId,
                        'quantity' => $qty,
                        'product_size_id' => $sizeId
                    ];
                }
            }
        }

        Ingredient::saveRecipe($productId, $recipeItems);
        echo json_encode(['success' => true]);
        exit;
    }
}
