<?php
// modules/pos/controllers/CustomerController.php
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../models/Customer.php';
require_once dirname(__DIR__, 3) . '/core/helpers/url.php';

class CustomerController {
    public function index() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (!Tenant::hasFeature('pos', 'customers')) {
            die('Upgrade to POS Standard or Premium to manage customers.');
        }

        if (!Auth::hasPermission('pos', 'read')) {
            die('No permission to view customers');
        }

        $customers = Customer::getAll();

        include __DIR__ . '/../views/customers.php';
    }

    public function create() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::hasPermission('pos', 'write')) {
            die('No permission to create customers');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
        } else {
            include __DIR__ . '/../views/customer_form.php';
        }
    }

    public function edit($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::hasPermission('pos', 'write')) {
            die('No permission to edit customers');
        }

        $customer = Customer::getById($id);
        if (!$customer) {
            die('Customer not found');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
        } else {
            include __DIR__ . '/../views/customer_form.php';
        }
    }

    public function delete($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::hasPermission('pos', 'delete')) {
            die('No permission to delete customers');
        }

        Customer::delete($id);
        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/customers');
        exit;
    }

    private function store() {
        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];

        Customer::create($data);
        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/customers');
        exit;
    }

    private function update($id) {
        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];

        Customer::update($id, $data);
        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/customers');
        exit;
    }
}
?>