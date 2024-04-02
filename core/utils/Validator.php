<?php

namespace Fckin\core\utils;

class Validation
{
    public function validate_required($value)
    {
        return !empty($value);
    }

    public function validate_email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public function validate_numeric($value)
    {
        return is_numeric($value);
    }

    public function validate_min_length($value, $min_length)
    {
        return strlen($value) >= $min_length;
    }

    public function validate_max_length($value, $max_length)
    {
        return strlen($value) <= $max_length;
    }
}
