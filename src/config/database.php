<?php
function createPDO(): ?PDO
{
    $config = [
        'host'     => 'localhost',
        'port'     => '5432',
        'dbname'   => 'travly',
        'username' => 'travler',
        'password' => 'travler21'
    ];

    try {
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return $pdo;

    } catch (PDOException $e) {
    
        error_log("[DB] Connection failed: " . $e->getMessage());
        
        if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
            echo "<div style='color:red;background:#fee;padding:10px;border:1px solid #f99;'>";
            echo "<strong>DB connection error:</strong><br>";
            echo htmlspecialchars($e->getMessage());
            echo "<br><small>DSN: " . htmlspecialchars($dsn) . "</small>";
            echo "</div>";
        }

        return null;
    }
}