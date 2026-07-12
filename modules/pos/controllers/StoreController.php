<?php
// modules/pos/controllers/StoreController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Store.php';
require_once __DIR__ . '/../../../core/classes/Auth.php';
require_once __DIR__ . '/../../../core/helpers/url.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';

class StoreController
{
    public function index()
    {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            die(__('no_permission'));
        }

        $tenantId = Tenant::getId();
        $stores = Store::getAll($tenantId);
        $currentStore = Store::getCurrent($tenantId);

        // For the management page, include the view
        $pageTitle = __('manage_stores');
        $activeNav = 'stores';
        $posUrl = function ($path) {
            $prefix = mc_base_path();
            $subdomain = Tenant::getCurrent()['subdomain'] ?? '';
            return $prefix . '/' . $subdomain . '/pos/' . ltrim($path, '/');
        };

        include __DIR__ . '/../views/stores.php';
    }

    /**
     * Switch current store (AJAX or redirect)
     */
    public function switch()
    {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        $storeId = $_GET['store_id'] ?? $_POST['store_id'] ?? null;

        if ($storeId && Store::setCurrent($storeId)) {
            // Redirect back or return JSON
            $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? null;
            if ($redirect) {
                header('Location: ' . $redirect);
                exit;
            }
            // Default: redirect to dashboard
            $prefix = mc_base_path();
            $subdomain = Tenant::getCurrent()['subdomain'] ?? '';
            header('Location: ' . $prefix . '/' . $subdomain . '/pos/dashboard');
            exit;
        }

        die(__('invalid_store'));
    }

    /**
     * Create a new store
     */
    public function create()
    {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            die(__('no_permission'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'    => trim($_POST['name'] ?? ''),
                'code'    => trim($_POST['code'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'phone'   => trim($_POST['phone'] ?? ''),
                'email'   => trim($_POST['email'] ?? ''),
            ];

            if (empty($data['name'])) {
                die(__('store_name_required'));
            }

            $tenantId = Tenant::getId();
            $storeId = Store::create($data, $tenantId);

            if ($storeId) {
                // Auto-create default category for this store
                $db = Database::getInstance();
                $db->insert('categories', [
                    'tenant_id' => $tenantId,
                    'store_id'  => $storeId,
                    'name'      => 'General',
                ]);
            }

            $prefix = mc_base_path();
            $subdomain = Tenant::getCurrent()['subdomain'] ?? '';
            header('Location: ' . $prefix . '/' . $subdomain . '/pos/stores');
            exit;
        }
    }

    /**
     * Update an existing store
     */
    public function update()
    {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            die(__('no_permission'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $storeId = $_POST['store_id'] ?? null;
            if (!$storeId) {
                die(__('invalid_store'));
            }

            $data = [
                'name'      => trim($_POST['name'] ?? ''),
                'code'      => trim($_POST['code'] ?? ''),
                'address'   => trim($_POST['address'] ?? ''),
                'phone'     => trim($_POST['phone'] ?? ''),
                'email'     => trim($_POST['email'] ?? ''),
                'is_default' => !empty($_POST['is_default']) ? 1 : 0,
            ];

            $tenantId = Tenant::getId();
            Store::update($storeId, $data, $tenantId);

            $prefix = mc_base_path();
            $subdomain = Tenant::getCurrent()['subdomain'] ?? '';
            header('Location: ' . $prefix . '/' . $subdomain . '/pos/stores');
            exit;
        }
    }

    /**
     * Delete (deactivate) a store
     */
    public function delete()
    {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            die(__('no_permission'));
        }

        $storeId = $_POST['store_id'] ?? $_GET['store_id'] ?? null;
        if (!$storeId) {
            die(__('invalid_store'));
        }

        $tenantId = Tenant::getId();
        $result = Store::delete($storeId, $tenantId);

        $prefix = mc_base_path();
        $subdomain = Tenant::getCurrent()['subdomain'] ?? '';
        header('Location: ' . $prefix . '/' . $subdomain . '/pos/stores');
        exit;
    }
}
