<?php
namespace Infrastructure\Logging;

class LogService {
  private static string $logFile;

  public static function info(string $message): void {
    self::writeLog('INFO', $message);
  }

  public static function error(string $message): void {
    self::writeLog('ERROR', $message);
  }

  public static function debug(string $message): void {
    self::writeLog('DEBUG', $message);
  }

  private static function writeLog(string $level, string $message): void {
    $logDir = sys_get_temp_dir();
    self::$logFile = $logDir . '/missao-roberto-backend.log';

    if (!is_dir($logDir)) {
      mkdir($logDir, 0777, true);
    }

    $logEntry = sprintf("[%s] [%s] %s%s", date('Y-m-d H:i:s'), $level, $message, PHP_EOL);
    file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
  }
}
