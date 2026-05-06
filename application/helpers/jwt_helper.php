<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists('generate_jwt')) {

    function generate_jwt($data)
    {
        $key = "S@urabh#JWT$" . 'SecretKey_2026_' . "!Secure123456";

        $payload = [
            'iss' => "localhost",
            'aud' => "localhost",
            'iat' => time(),
            'exp' => time() + (60 * 60), // 1 hour
            'data' => $data
        ];

        return JWT::encode($payload, $key, 'HS256');
    }
}

if (!function_exists('verify_jwt')) {

    function verify_jwt($token)
    {
        $key = "your_secret_key";

        try {
            return JWT::decode($token, new Key($key, 'HS256'));
        } catch (Exception $e) {
            return false;
        }
    }
}
