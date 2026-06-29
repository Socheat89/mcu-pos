<?php
// public/api/TransactionLogger.php
// Centralized handling for transaction logs to prevent 'Split Brain' issues

class TransactionLogger {
    private static function resolvePath() {
        // ABSOLUTE SOURCE OF TRUTH:
        // We look for the 'logs' folder at the specific relative location from THIS file.
        // public/api/TransactionLogger.php -> ../../logs/ -> root/logs/
        
        $baseDir = dirname(__DIR__, 2); // Go up 2 levels from public/api
        $logDir = $baseDir . '/logs';
        $logFile = $logDir . '/transactions.json';
        
        // Ensure dir exists
        if (!is_dir($logDir)) {
            if (!@mkdir($logDir, 0777, true)) {
                // Return null if we can't create it (permission issue)
                return null;
            }
        }
        
        // Ensure file exists
        if (!file_exists($logFile)) {
            file_put_contents($logFile, json_encode([]));
        }
        
        return $logFile;
    }

    public static function get($ref = null) {
        $path = self::resolvePath();
        if (!$path || !file_exists($path)) return [];

        $json = json_decode(file_get_contents($path), true);
        if (!is_array($json)) return [];

        if ($ref) return $json[$ref] ?? null;
        return $json;
    }

    public static function save($ref, $data) {
        $path = self::resolvePath();
        if (!$path) return false;

        $json = self::get(); // valid array
        
        // Merge if exists
        if (isset($json[$ref])) {
            $json[$ref] = array_merge($json[$ref], $data);
        } else {
            $json[$ref] = $data;
        }

        return file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT));
    }
    
    public static function getPath() {
        return self::resolvePath();
    }
}
?>
