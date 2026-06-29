<?php
// core/classes/Tenant.php
class Tenant {
    private static $currentTenant = null;

    public static function detect($tenantSlug = null) {
        if (self::$currentTenant !== null) {
            return self::$currentTenant;
        }

        $host = $_SERVER['HTTP_HOST'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Check for subdomain (e.g., shop1.mysaas.com)
        $parts = explode('.', $host);
        if (count($parts) > 2) {
            $subdomain = $parts[0];
            $tenant = self::findBySubdomain($subdomain);
            if ($tenant) {
                self::$currentTenant = $tenant;
                return $tenant;
            }
        }

        // Check for URL path (e.g., /shop1/...) - use provided slug or parse
        if ($tenantSlug) {
            $tenant = self::findBySubdomain($tenantSlug);
            if ($tenant) {
                self::$currentTenant = $tenant;
                return $tenant;
            }
        } else {
            // Fallback parsing (for backward compatibility)
            if (preg_match('/^\/([^\/]+)\//', $requestUri, $matches)) {
                $pathTenant = $matches[1];
                $tenant = self::findBySubdomain($pathTenant);
                if ($tenant) {
                    self::$currentTenant = $tenant;
                    return $tenant;
                }
            }
        }

        // Default tenant or error
        throw new Exception('Tenant not found');
    }

    private static function findBySubdomain($subdomain) {
        $db = Database::getInstance();
        return $db->fetchOne("SELECT * FROM tenants WHERE subdomain = ? AND status = 'active'", [$subdomain]);
    }

    public static function getCurrent() {
        return self::$currentTenant;
    }

    public static function getId() {
        return self::$currentTenant ? self::$currentTenant['id'] : null;
    }

    public static function hasSystem($systemName) {
        if (!self::$currentTenant) return false;
        
        // Special case for generic "POS System" check
        if ($systemName === 'POS System') {
            return self::getPosLevel() > 0;
        }

        self::checkExpirations();

        $db = Database::getInstance();
        $count = $db->fetchOne(
            "SELECT COUNT(*) as count FROM tenant_systems ts 
             JOIN systems s ON ts.system_id = s.id 
             WHERE ts.tenant_id = ? AND s.name = ? 
             AND ts.status = 'active' AND (ts.expires_at IS NULL OR ts.expires_at > NOW())",
            [self::getId(), $systemName]
        );
        return $count['count'] > 0;
    }

    public static function hasModule($moduleName) {
        if (!self::$currentTenant) return false;

        self::checkExpirations();

        $db = Database::getInstance();
        // Check if any of the subscribed systems (plans) have this module linked
        $count = $db->fetchOne(
            "SELECT COUNT(*) as count FROM tenant_systems ts 
             JOIN systems s ON ts.system_id = s.id 
             JOIN system_modules sm ON sm.system_id = s.id
             WHERE ts.tenant_id = ? AND sm.module_name = ? 
             AND ts.status = 'active' AND (ts.expires_at IS NULL OR ts.expires_at > NOW())",
            [self::getId(), $moduleName]
        );
        
        return $count['count'] > 0;
    }

    public static function hasFeature($moduleName, $featureKey) {
        if (!self::$currentTenant) return false;

        $db = Database::getInstance();
        
        // 1. Check for Overrides (tenant_features)
        $override = $db->fetchOne(
            "SELECT action FROM tenant_features WHERE tenant_id = ? AND module_name = ? AND feature_key = ?",
            [self::getId(), $moduleName, $featureKey]
        );

        if ($override) {
            return $override['action'] === 'grant';
        }

        // 2. Standard Plan Check
        self::checkExpirations();

        $count = $db->fetchOne(
            "SELECT COUNT(*) as count FROM tenant_systems ts 
             JOIN system_modules sm ON sm.system_id = ts.system_id
             WHERE ts.tenant_id = ? 
             AND sm.module_name = ? AND sm.feature_key = ?
             AND ts.status = 'active' AND (ts.expires_at IS NULL OR ts.expires_at > NOW())",
            [self::getId(), $moduleName, $featureKey]
        );
        
        return $count['count'] > 0;
    }

    public static function getPosLevel() {
        if (!self::$currentTenant) return 0;
        
        self::checkExpirations();

        $db = Database::getInstance();
        $systems = $db->fetchAll(
            "SELECT s.name FROM tenant_systems ts 
             JOIN systems s ON ts.system_id = s.id 
             WHERE ts.tenant_id = ? AND ts.status = 'active' 
             AND (ts.expires_at IS NULL OR ts.expires_at > NOW())
             AND s.name LIKE 'POS %'",
            [self::getId()]
        );
        
        $level = 0;
        foreach ($systems as $system) {
            if ($system['name'] == 'POS Basic') $level = max($level, 1);
            if ($system['name'] == 'POS Standard') $level = max($level, 2);
            if ($system['name'] == 'POS Premium') $level = max($level, 3);
        }
        return $level;
    }

    private static function checkExpirations() {
        if (!self::$currentTenant) return;
        
        $db = Database::getInstance();
        // Auto-expire anything that passed the date
        $db->query(
            "UPDATE tenant_systems SET status = 'expired' 
             WHERE tenant_id = ? AND status = 'active' 
             AND expires_at IS NOT NULL AND expires_at <= NOW()",
            [self::getId()]
        );
    }

    public static function setCurrent($tenant) {
        self::$currentTenant = $tenant;
    }
}
?>