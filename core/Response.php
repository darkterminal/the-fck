<?php

namespace Fckin\core;

class Response
{
    public function setStatusCode(int $code)
    {
        http_response_code($code);
    }

    public function redirect(string $path, $replace = true, $response_code = 302)
    {
        header("Location: $path", $replace, $response_code);
    }

    public function hx_trigger(string $name, array $metadata): void
    {
        $data[$name] = $metadata;
        header("HX-Trigger: " . json_encode($data));
    }

    public function hx_pushUrl(string $path): void
    {
        header("HX-Push-Url: {$path}");
    }

    public function hx_redirect(string $path): void
    {
        header("HX-Redirect: {$path}");
    }

    public function hx_target(string $target): void
    {
        header("HX-Target: {$target}");
    }

    public function hx_refresh(bool $refresh = true): void
    {
        header("HX-Refresh: " . ($refresh ? "true" : "false"));
    }

    public function hx_replaceUrl(string $url): void
    {
        header("HX-Replace-Url: {$url}");
    }

    public function hx_reswap(string $value): void
    {
        header("HX-Reswap: {$value}");
    }

    public function hx_retarget(string $selector): void
    {
        header("HX-Retarget: {$selector}");
    }

    public function hx_reselect(string $selector): void
    {
        header("HX-Reselect: {$selector}");
    }

    public function hx_triggerAfterSettle(string $event): void
    {
        header("HX-Trigger-After-Settle: {$event}");
    }

    public function hx_triggerAfterSwap(string $event): void
    {
        header("HX-Trigger-After-Swap: {$event}");
    }
}
