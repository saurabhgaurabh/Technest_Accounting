<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function test_token($r)
{
    return sha1(mt_rand(00000, 99999));
}
function generate_jwt($data)
{
    $key = "TechNest@2026#SecureKey";
    $payload = [
        'iat'  => time(),
        'exp'  => time() + (60 * 60 * 24), // 24 hours
        'data' => $data
    ];
    return JWT::encode($payload, $key, 'HS256');
}
