<?php

namespace App\Support;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTConfig
{
    private static $secretKey = 'YOUR_SECRET_KEY'; // Cambia esto por una clave secreta segura
    private static $algorithm = 'HS256';
    private static $tokenLifetime = 86400; // 24 hours

    public static function generateToken($userId)
    {
        $payload = [
            'iss' => 'your-app', // Issuer
            'iat' => time(),     // Issued at
            'exp' => time() + self::$tokenLifetime, // Expiration time
            'sub' => $userId    // Subject (User ID)
        ];

        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    public static function decodeToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null; // Token invalid
        }
    }
}
