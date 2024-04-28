<?php

namespace Fckin\core\utils;

class Session
{
    protected const FORM_VALUES_KEY = 'form_values';

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function set_bulk(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = '')
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function remove_bulk(array $data)
    {
        foreach ($data as $key) {
            $this->remove($key);
        }
    }

    public function flash($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $_SESSION['_flash'][$key] = $value;
            }
        } else {
            $key = func_get_arg(0);
            $value = func_get_arg(1);
            if (!empty($value)) {
                $_SESSION['_flash'][$key] = $value;
            } else {
                $flash = $this->get('_flash', []);
                $value = $this->get("_flash.$key");
                $this->remove("_flash.$key");
                $this->set('_flash', $flash);
                return $value;
            }
        }
    }

    public static function setBulkFormValues(array $data)
    {
        foreach ($data as $key => $value) {
            self::setFormValue($key, $value);
        }
    }

    public static function setFormValue($fieldName, $value)
    {
        $_SESSION[self::FORM_VALUES_KEY][$fieldName] = $value;
    }

    public static function formValue($fieldName)
    {
        return $_SESSION[self::FORM_VALUES_KEY][$fieldName] ?? '';
    }

    public static function clearFormValues()
    {
        unset($_SESSION[self::FORM_VALUES_KEY]);
    }
}
