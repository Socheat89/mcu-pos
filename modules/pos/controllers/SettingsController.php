<?php
require_once __DIR__ . '/../../../core/classes/Settings.php';
require_once __DIR__ . '/../../../core/classes/User.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/helpers/url.php';

class SettingsController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Add middleware checks if they are available/standard
        // Assuming they are needed as in other controllers
        require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
        require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (class_exists('Tenant') && Tenant::getPosLevel() < 1) {
             die('POS access required.');
        }

        $tenantId = Tenant::getId();
        $settings = Settings::getAll($tenantId);
        $users = User::getAll($tenantId);
        
        // Ensure defaults if not present
        $defaults = [
            'receipt_show_logo' => '1',
            'receipt_logo_path' => '',
            'receipt_header_text' => 'Point of Sale Receipt',
            'receipt_footer_text' => 'Thank you for your business!',
            'receipt_font_size' => '12',
            'receipt_paper_width' => '400',
            'pos_allowed_users' => '[]', // JSON array of allowed user IDs
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'company_tax_id' => '',
            'company_website' => '',
            'pos_method_cash_enabled' => '1',
            'pos_method_khqr_enabled' => '1',
            'pos_method_khqr_image' => mc_url('public/images/khqr_preview.png'),
            'pos_method_card_enabled' => '1',
            'pos_method_transfer_enabled' => '1'
        ];

        foreach ($defaults as $key => $default) {
            if (!isset($settings[$key])) {
                $settings[$key] = $default;
            }
        }
        
        require __DIR__ . '/../views/settings/index.php';
    }

    public function update() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (class_exists('Tenant') && Tenant::getPosLevel() < 1) {
             die('POS access required.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $tenantId = Tenant::getId();
             
             // Settings keys to look for in POST
             $keys = [
                 'receipt_logo_path',
                 'receipt_header_text',
                 'receipt_footer_text',
                 'receipt_font_size',
                 'receipt_paper_width',
                 'company_address',
                 'company_phone',
                 'company_email',
                 'company_tax_id',
                 'company_website'
             ];

             $checkboxes = [
                 'pos_method_cash_enabled',
                 'pos_method_khqr_enabled',
                 'pos_method_card_enabled',
                 'pos_method_transfer_enabled'
             ];

             foreach ($checkboxes as $chk) {
                 Settings::set($chk, isset($_POST[$chk]) ? '1' : '0', $tenantId);
             }

             foreach ($keys as $key) {
                 // Restrict receipt settings for Level 1
                 if (strpos($key, 'receipt_') === 0 && Tenant::getPosLevel() < 2) {
                     continue;
                 }
                 
                 if (isset($_POST[$key])) {
                     Settings::set($key, $_POST[$key], $tenantId);
                 }
             }
             
             // Handle checkboxes
             if (Tenant::getPosLevel() >= 2) {
                 $showLogo = isset($_POST['receipt_show_logo']) ? '1' : '0';
                 Settings::set('receipt_show_logo', $showLogo, $tenantId);
             }
             
             // Handle allowed users
             $allowedUsers = isset($_POST['pos_allowed_users']) ? $_POST['pos_allowed_users'] : [];
             // Ensure it's stored as JSON
             Settings::set('pos_allowed_users', json_encode($allowedUsers), $tenantId);
             
             // Handle Logo Upload if present
             if (isset($_FILES['logo_upload']) && $_FILES['logo_upload']['error'] === UPLOAD_ERR_OK) {
                 $uploadDir = __DIR__ . '/../../../public/uploads/tenants/' . $tenantId . '/';
                 if (!is_dir($uploadDir)) {
                     mkdir($uploadDir, 0777, true);
                 }
                 
                 $fileName = 'logo_' . time() . '_' . basename($_FILES['logo_upload']['name']);
                 $targetPath = $uploadDir . $fileName;
                 
                 if (move_uploaded_file($_FILES['logo_upload']['tmp_name'], $targetPath)) {
                     $webPath = mc_url('public/uploads/tenants/' . $tenantId . '/' . $fileName);
                     Settings::set('receipt_logo_path', $webPath, $tenantId);
                 }
             }

             // Handle KHQR Image Upload if present
             if (isset($_FILES['khqr_upload']) && $_FILES['khqr_upload']['error'] === UPLOAD_ERR_OK) {
                 $uploadDir = __DIR__ . '/../../../public/uploads/tenants/' . $tenantId . '/';
                 if (!is_dir($uploadDir)) {
                     mkdir($uploadDir, 0777, true);
                 }
                 
                 $fileName = 'khqr_' . time() . '_' . basename($_FILES['khqr_upload']['name']);
                 $targetPath = $uploadDir . $fileName;
                 
                 if (move_uploaded_file($_FILES['khqr_upload']['tmp_name'], $targetPath)) {
                     $webPath = mc_url('public/uploads/tenants/' . $tenantId . '/' . $fileName);
                     Settings::set('pos_method_khqr_image', $webPath, $tenantId);
                 }
             }

             // Redirect back
             $subdomain = $_SESSION['tenant_subdomain'] ?? 'default';
             header('Location: ' . mc_url($subdomain . '/pos/settings?success=1'));
             exit;
        }
    }
}
