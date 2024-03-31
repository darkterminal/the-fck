<?php

namespace Fckin\core;

class Request
{
    public $params = [];

    public function getPath()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position === false) {
            return $path;
        }
        return substr($path, 0, $position);
    }

    public function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet()
    {
        return $this->method() === 'get';
    }

    public function isPost()
    {
        return $this->method() === 'post';
    }

    public function isPut()
    {
        return $this->method() === 'put';
    }

    public function isDelete()
    {
        return $this->method() === 'delete';
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($key)
    {
        return $this->params[$key];
    }

    public function getQueries(): array
    {
        return $_GET;
    }

    public function getQuery($key): string
    {
        return $_GET[$key];
    }

    public function getBody()
    {
        $body = [];

        if ($this->method() === 'get') {
            foreach ($_GET as $key => $val) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if ($this->method() === 'post') {
            foreach ($_POST as $key => $val) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        // Handle JSON payload for PUT and DELETE requests
        if (in_array($this->method(), ['put', 'delete'])) {
            $input = file_get_contents('php://input');
            $jsonBody = json_decode($input, true);

            if ($jsonBody) {
                $body = array_merge($body, $jsonBody);
            }
        }

        return $body;
    }
}
