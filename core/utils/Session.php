<?php

namespace Fckin\core\utils;

class Session
{
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function session_set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function session_get($key, $default = null)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public function session_has($key)
    {
        return isset($_SESSION[$key]);
    }

    public function session_remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function session_flash($key, $value = null)
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
        } else {
            $flash = $this->session_get('_flash', []);
            $value = $this->session_get("_flash.$key");
            $this->session_remove("_flash.$key");
            $this->session_set('_flash', $flash);
            return $value;
        }
    }
}
