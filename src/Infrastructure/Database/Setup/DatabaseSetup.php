<?php
namespace Infrastructure\Database\Setup;

use PDO;
use Exception;

use Infrastructure\Database\Config\DatabaseConfig;
use Infrastructure\Database\Connection\DatabaseConnection;

use Infrastructure\Logging\LogService;

class DatabaseSetup {
  private PDO $pdo;
  private array $dbConfig;

  public function __construct(PDO $pdo) {
    $this->pdo = $pdo;
    $this->dbConfig = DatabaseConfig::getCredentials();
  }

  public function run() {
    try {
      $this->pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbConfig['dbname']}`");
      $this->pdo->exec("USE `{$this->dbConfig['dbname']}`");

      $this->runSqlScript(__DIR__ . '/init.sql');

      LogService::info("Database setup completed successfully!\n");
    } catch (Exception $e) {
      LogService::error($e->getMessage());
      die("Database setup failed: " . $e->getMessage());
    }
  }

  public static function reset(): void {
    $pdo = DatabaseConnection::getConnection();

    try {
      $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

      $pdo->exec("TRUNCATE TABLE customer_address");
      $pdo->exec("TRUNCATE TABLE customer");

      $pdo->exec("ALTER TABLE customer AUTO_INCREMENT = 1");
      $pdo->exec("ALTER TABLE customer_address AUTO_INCREMENT = 1");

      $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    } catch (Exception $e) {
      LogService::error($e->getMessage());
      throw $e;
    }
  }

  private function runSqlScript(string $filePath): void {
    $sql = file_get_contents($filePath);
    $this->pdo->exec($sql);
  }
}
