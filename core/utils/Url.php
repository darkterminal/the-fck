<?php

namespace Fckin\core\utils;

class URLHelper
{
    protected $baseURL;

    public function __construct($baseURL = '')
    {
        $this->baseURL = empty($baseURL) ? base_url() : $baseURL;
    }

    public function url($path)
    {
        return $this->baseURL . ltrim($path, '/');
    }

    public function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    public function asset($path)
    {
        return $this->url("/assets/$path");
    }
}
