<?php
namespace Infrastructure\Api\Auth\Seeder;

use Infrastructure\Api\Auth\Middleware\JWTMiddleware;

use Exception;

class AuthUserSeeder {
  public function seed(): void {
    $TOKEN_FILE_PATH = __DIR__ . '/../test_jwt_token.txt';
    $TOKEN_EXPIRATION = 7 * 24 * 60 * 60;

    $email = 'test_user@seeding.com';
    $password = password_hash('AlmostSecurePass123!', PASSWORD_BCRYPT);

    $payload = [
      "email" => $email,
      "exp" => time() + $TOKEN_EXPIRATION
    ];

    $token = JWTMiddleware::encode($payload, $_ENV['NEXTAUTH_SECRET']);

    self::saveTokenToFile($token, $TOKEN_FILE_PATH);
  }

  private static function saveTokenToFile(string $token, string $path): void {
    if (!file_put_contents($path, $token))
      throw new Exception("Failed to write token file."); 
  }
}
