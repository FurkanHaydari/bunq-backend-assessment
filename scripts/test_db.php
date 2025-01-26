<?php
declare(strict_types=1);

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../database/chat.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Found tables:\n";
    print_r($tables);
} catch (PDOException $e) {
    die("Database test failed: " . $e->getMessage() . "\n");
}
