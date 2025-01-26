<?php
declare(strict_types=1);

$dbPath = __DIR__ . '/../database/chat.db';
$dbDir = dirname($dbPath);

// Create database directory if it doesn't exist
if (!file_exists($dbDir)) {
    if (!mkdir($dbDir, 0755, true)) {
        die("Failed to create database directory\n");
    }
    echo "Created database directory\n";
}

// Initialize database
try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Run schema
    $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
    $pdo->exec($schema);
    
    echo "Database initialized successfully\n";
} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage() . "\n");
}
