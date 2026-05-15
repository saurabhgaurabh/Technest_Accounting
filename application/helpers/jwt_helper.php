<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists('generate_jwt')) {
    function generate_jwt($user_data)
    {
        // 🔐 Change this to a random, complex string. Keep it secret!
        $secret_key = "Your_Super_Secret_Billing_App_Key_2026"; 
        
        $issued_at = time();
        $expire_time = $issued_at + (60 * 60 * 24); // Token valid for 24 hours

        $payload = array(
            'iss'  => base_url(),         // Issuer
            'iat'  => $issued_at,         // Issued at
            'exp'  => $expire_time,        // Expiration time
            'data' => [
                'user_id'  => $user_data['user_id'],
                'email'    => $user_data['email'],
                'username' => $user_data['username']
            ]
        );

        // Encode the payload into a JWT token using HS256 algorithm
        return JWT::encode($payload, $secret_key, 'HS256');
    }
}