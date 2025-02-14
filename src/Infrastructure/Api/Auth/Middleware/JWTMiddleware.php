<?php
namespace Infrastructure\Api\Auth\Middleware;

use Exception;

class JWTMiddleware {
  private static string $algorithm = 'HS256';

  public static function encode(array $payload, string $secret, int $exp = 3600): string {
    $header = ['alg' => self::$algorithm, 'typ' => 'JWT'];
    $payload['exp'] = time() + $exp;

    $base64Header = self::base64UrlEncode(json_encode($header));
    $base64Payload = self::base64UrlEncode(json_encode($payload));
    $signature = self::sign("$base64Header.$base64Payload", $secret);

    return "$base64Header.$base64Payload.$signature";
  }

  public static function decode(string $token, string $secret): array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) 
      throw new Exception("Invalid token format");

    [$base64Header, $base64Payload, $signature] = $parts;
    $payload = json_decode(self::base64UrlDecode($base64Payload), true);

    if (isset($payload['exp']) && $payload['exp'] < time()) 
      throw new Exception("Token expired");

    if (!hash_equals(self::sign("$base64Header.$base64Payload", $secret), $signature)) 
      throw new Exception("Invalid signature");

    return $payload;
  }

  private static function sign(string $data, string $secret): string {
    return self::base64UrlEncode(hash_hmac('sha256', $data, $secret, true));
  }

  private static function base64UrlEncode(string $data): string {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
  }

  private static function base64UrlDecode(string $data): string {
    $padding = strlen($data) % 4;
    if ($padding) 
      $data .= str_repeat('=', 4 - $padding);

    return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
  }
}
