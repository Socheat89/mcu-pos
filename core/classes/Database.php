<?php
// core/classes/Database.php
<<<<<<< HEAD
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
=======
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        $config = require __DIR__ . '/../../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $this->pdo = new PDO($dsn, $config['username'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Keep DB session timezone aligned with application timezone (Asia/Phnom_Penh, UTC+7)
        // Using offset avoids reliance on MySQL timezone tables.
        $this->pdo->exec("SET time_zone = '+07:00'");
    }

<<<<<<< HEAD
    public static function getInstance() {
=======
    public static function getInstance()
    {
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

<<<<<<< HEAD
    public function getConnection() {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
=======
    public function getConnection()
    {
        return $this->pdo;
    }

    public function query($sql, $params = [])
    {
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

<<<<<<< HEAD
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function insert($table, $data) {
=======
    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    public function insert($table, $data)
    {
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        return $this->pdo->lastInsertId();
    }

<<<<<<< HEAD
    public function update($table, $data, $where, $whereParams = []) {
=======
    public function update($table, $data, $where, $whereParams = [])
    {
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $setStr = implode(', ', $set);
<<<<<<< HEAD
        
        // Convert positional WHERE parameters to named parameters
        $namedWhereParams = [];
        $paramIndex = 0;
        $namedWhere = preg_replace_callback('/\?/', function($match) use ($whereParams, &$paramIndex, &$namedWhereParams) {
=======

        // Convert positional WHERE parameters to named parameters
        $namedWhereParams = [];
        $paramIndex = 0;
        $namedWhere = preg_replace_callback('/\?/', function ($match) use ($whereParams, &$paramIndex, &$namedWhereParams) {
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
            $paramName = ":where{$paramIndex}";
            $namedWhereParams[$paramName] = $whereParams[$paramIndex];
            $paramIndex++;
            return $paramName;
        }, $where);
<<<<<<< HEAD
        
=======

>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$namedWhere}";
        $params = array_merge($data, $namedWhereParams);
        return $this->query($sql, $params)->rowCount();
    }

<<<<<<< HEAD
    public function delete($table, $where, $params = []) {
=======
    public function delete($table, $where, $params = [])
    {
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }
}
?>