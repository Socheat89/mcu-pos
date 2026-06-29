<?php
// core/classes/Settings.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Tenant.php';

class Settings {
    private static $db;

    private static function getDb() {
        if (!self::$db) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    public static function get($key, $tenantId = null, $default = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $setting = self::getDb()->fetchOne(
            "SELECT value FROM settings WHERE tenant_id = ? AND key_name = ?",
            [$tenantId, $key]
        );
        return $setting ? $setting['value'] : $default;
    }

    public static function set($key, $value, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $existing = self::getDb()->fetchOne(
            "SELECT id FROM settings WHERE tenant_id = ? AND key_name = ?",
            [$tenantId, $key]
        );
        if ($existing) {
            self::getDb()->query(
                "UPDATE settings SET value = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$value, $existing['id']]
            );
        } else {
            self::getDb()->query(
                "INSERT INTO settings (tenant_id, key_name, value) VALUES (?, ?, ?)",
                [$tenantId, $key, $value]
            );
        }
    }

    public static function getAll($tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $settings = self::getDb()->fetchAll(
            "SELECT key_name, value FROM settings WHERE tenant_id = ?",
            [$tenantId]
        );
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key_name']] = $setting['value'];
        }
        return $result;
    }

    public static function initializeDefaults($tenantId) {
        $defaults = [
            'max_free_users' => '2',
            'receipt_show_logo' => '1',
            'receipt_header_text' => 'Point of Sale Receipt',
            'receipt_footer_text' => 'Thank you for your business!',
            'receipt_font_size' => '12',
            'receipt_paper_width' => '400',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'company_tax_id' => '',
            'company_website' => ''
        ];

        foreach ($defaults as $key => $value) {
            self::set($key, $value, $tenantId);
        }
    }
}
?>