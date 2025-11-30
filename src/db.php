<?php

function getPDO(): PDO {
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

        return $pdo;
        
    } catch (PDOException $e) {
         // 🔍 Выводим ошибку в браузер (ТОЛЬКО для разработки!)
         echo "<pre>";
         echo "❌ ОШИБКА ПОДКЛЮЧЕНИЯ:\n";
         echo "DSN: $dsn\n";
         echo "User: {$db['username']}\n";
         echo "Error: " . $e->getMessage() . "\n";
         echo "</pre>";
         exit;
    }
}
