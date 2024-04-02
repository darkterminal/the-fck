<?php

namespace Fckin\core\utils;

use DateTime;

class Common
{
    public function slugify($text)
    {
        // Remove special characters
        $text = preg_replace('/[^a-z0-9-]/i', '-', $text);
        // Convert to lowercase
        $text = strtolower($text);
        // Remove extra dashes
        $text = preg_replace('/-+/', '-', $text);
        // Trim dashes from the beginning and end
        $text = trim($text, '-');
        return $text;
    }

    public function format_date($date, $format = 'Y-m-d H:i:s')
    {
        $datetime = new DateTime($date);
        return $datetime->format($format);
    }

    public function truncate_text($text, $length, $ellipsis = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return rtrim(substr($text, 0, $length)) . $ellipsis;
    }
}
