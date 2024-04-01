<?php

namespace Fckin\core\utils;

class Security
{
    public function sanitize_input($input)
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    public function csrf_token()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function verify_csrf_token($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function encrypt($data, $key)
    {
        return openssl_encrypt($data, 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16));
    }

    public function decrypt($data, $key)
    {
        return openssl_decrypt($data, 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16));
    }
}
