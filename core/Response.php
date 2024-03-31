<?php
namespace Fckin\core;

class Response {
    public function setStatusCode(int $code) {
        http_response_code($code);
    }

    public function redirect(string $path, $replace = true, $response_code = 302) {
        header("Location: $path", $replace, $response_code);
    }
}
