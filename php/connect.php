<?php
function getPDO() {
    $db = [
        'host' => 'localhost',
        'port' => '5432',
        'dbname' => 'travly',
        'username' => 'travler',
        'password' => 'travler21'
    ];
    
    try {
        $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$db['dbname']}";
        $pdo = new PDO($dsn, $db['username'], $db['password']);
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        echo "norm";
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        echo "ne norm";
    }
}

getPDO();
?>
