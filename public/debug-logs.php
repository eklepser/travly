<?php
// Временная страница для просмотра логов и отладки
// УДАЛИТЕ ЭТОТ ФАЙЛ ПОСЛЕ ОТЛАДКИ!

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Проверка на localhost
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);

if (!$isLocalhost) {
    die('Доступ только с localhost');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отладка - Логи</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #1e1e1e;
            color: #d4d4d4;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            background: #252526;
            border-radius: 5px;
        }
        h2 {
            color: #4ec9b0;
            margin-top: 0;
        }
        pre {
            background: #1e1e1e;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
            max-height: 400px;
            overflow-y: auto;
        }
        .error { color: #f48771; }
        .success { color: #4ec9b0; }
        .info { color: #569cd6; }
        .warning { color: #dcdcaa; }
    </style>
</head>
<body>
    <h1>🔍 Отладка - Информация о логах</h1>

    <div class="section">
        <h2>📁 Пути к логам PHP</h2>
        <pre>
error_log: <?= ini_get('error_log') ?: 'Не установлен' ?>

display_errors: <?= ini_get('display_errors') ? 'Включено' : 'Выключено' ?>
log_errors: <?= ini_get('log_errors') ? 'Включено' : 'Выключено' ?>
error_reporting: <?= error_reporting() ?>
        </pre>
    </div>

    <div class="section">
        <h2>📂 Возможные расположения логов</h2>
        <pre>
<?php
$possiblePaths = [
    // OpenServer
    'C:/OpenServer/userdata/logs/',
    'C:/OpenServer/logs/',
    
    // XAMPP
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/php/logs/php_error_log',
    
    // Другие варианты
    ini_get('error_log'),
    __DIR__ . '/../logs/error.log',
    __DIR__ . '/error.log',
];

echo "Проверяем следующие пути:\n\n";
foreach ($possiblePaths as $path) {
    if ($path) {
        $exists = file_exists($path) ? '✅ СУЩЕСТВУЕТ' : '❌ не найден';
        $readable = file_exists($path) && is_readable($path) ? ' (читаемый)' : '';
        echo $exists . $readable . ": " . $path . "\n";
        
        if (file_exists($path) && is_readable($path)) {
            if (is_dir($path)) {
                echo "   (это директория)\n";
            } else {
                $size = filesize($path);
                echo "   Размер: " . number_format($size) . " байт\n";
            }
        }
    }
}
?>
        </pre>
    </div>

    <div class="section">
        <h2>📋 Последние записи из error_log (если доступен)</h2>
        <pre>
<?php
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog) && is_readable($errorLog)) {
    $lines = file($errorLog);
    $lastLines = array_slice($lines, -50); // Последние 50 строк
    echo htmlspecialchars(implode('', $lastLines));
} else {
    echo "Лог-файл недоступен для чтения.\n";
    echo "Попробуйте найти логи вручную в:\n";
    echo "- OpenServer: C:/OpenServer/userdata/logs/\n";
    echo "- XAMPP: C:/xampp/apache/logs/error.log\n";
    echo "- Или проверьте настройки PHP (php.ini)\n";
}
?>
        </pre>
    </div>

    <div class="section">
        <h2>🔧 Тест записи в лог</h2>
        <pre>
<?php
$testMessage = "[DEBUG-TEST] " . date('Y-m-d H:i:s') . " - Тестовая запись из debug-logs.php\n";
error_log($testMessage);
echo "Отправлено сообщение в лог: " . htmlspecialchars($testMessage);
echo "\n\nПроверьте лог-файл выше - там должна появиться эта запись.";
?>
        </pre>
    </div>

    <div class="section">
        <h2>💡 Как найти логи вручную</h2>
        <pre>
<strong>OpenServer:</strong>
1. Откройте панель OpenServer
2. Нажмите "Настройки" → "Логи"
3. Или проверьте: C:/OpenServer/userdata/logs/

<strong>XAMPP:</strong>
1. Откройте: C:/xampp/apache/logs/error.log
2. Или: C:/xampp/php/logs/php_error_log

<strong>Другие серверы:</strong>
- Проверьте php.ini: error_log = ...
- Или используйте команду: php -i | findstr error_log
        </pre>
    </div>

    <div class="section">
        <h2>⚠️ ВАЖНО</h2>
        <p class="warning">Удалите этот файл (debug-logs.php) после отладки!</p>
    </div>
</body>
</html>

