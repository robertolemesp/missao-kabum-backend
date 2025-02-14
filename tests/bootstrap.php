<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Infrastructure\Database\Setup\DatabaseSetup;
use Infrastructure\Database\Config\DatabaseConfig;

try {
  $dbConfig = DatabaseConfig::getCredentials();
  $dsn = "mysql:host={$dbConfig['host']};charset=utf8mb4";
  $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  $databaseSetup = new DatabaseSetup($pdo);
  $databaseSetup->run();
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
