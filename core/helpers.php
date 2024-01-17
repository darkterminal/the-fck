<?php

use Fckin\core\Application;
use Fckin\core\FTAuth;
use Symfony\Component\VarDumper\VarDumper;

function thefck(...$vars)
{
    static $first = true;
    if ($first) {
        $first = false;
        echo '<html><body>';
    }
    foreach ($vars as $var) {
        VarDumper::dump($var);
    }
}

function twMerge(string $baseClass, string|array $additionalClasses = [])
{
    $baseClasses = explode(' ', $baseClass);
    $additionalClasses = is_string($additionalClasses) ? explode(' ', $additionalClasses) : $additionalClasses;

    $mergedClasses = array_unique(array_merge($baseClasses, $additionalClasses));

    $filteredClasses = array_filter($mergedClasses);

    return implode(' ', $filteredClasses);
}

function isGuest()
{
    return !isset($_COOKIE['FTA_TOKEN']);
}

function unAuthorized()
{
    $auth = new FTAuth(env('FTA_SECRET'));
    return $auth->unsetAuth();
}

function isAuthenticate()
{
    $auth = new FTAuth(env('FTA_SECRET'));
    return $auth->isAuthenticate();
}

function getAuthData()
{
    $auth = new FTAuth(env('FTA_SECRET'));
    return $auth->getData();
}

function addToast($key, $message)
{
    Application::$app->session->setFlashMessage($key, $message);
}

function toast($key)
{
    if (Application::$app->session->getFlashMessage($key)) :
        return '<div role="alert" class="alert alert-' . $key . ' w-[30rem] mx-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>' . Application::$app->session->getFlashMessage($key) . '</span>
        </div>';
    endif;
}

function display_info()
{
    echo phpinfo();
    die();
}

function text_alt_formatter($input)
{
    $snakeCase = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));

    $exploded = explode('_', $snakeCase);

    $capitalizedWords = array_map('ucfirst', $exploded);

    return implode(' ', $capitalizedWords);
}

function colorize($text, $color)
{
    $colors = [
        'yellow' => '43',
        'green' => '42',
        'red' => '41',
    ];

    if (!isset($colors[$color])) {
        die("Invalid color specified");
    }

    $colorCode = $colors[$color];
    $resetCode = "\033[0m";

    return " \033[{$colorCode};30m {$text} {$resetCode}";
}

function env($key, $default = null)
{
    if (is_null($default)) {
        return php_sapi_name() === 'cli' ? getenv($key) : $_ENV[$key];
    } else {
        return php_sapi_name() === 'cli' ? (getenv($key) ?? $default) : ($_ENV[$key] ?? $default);
    }
}

function config($key)
{
    $configPath = Application::$ROOT_DIR . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php';

    if (!file_exists($configPath)) {
        return $key . " doesn't exist!";
    }

    require $configPath;

    $value = $config;

    $keys = explode('.', $key);

    foreach ($keys as $nestedKey) {
        if (isset($value[$nestedKey])) {
            $value = $value[$nestedKey];
        } else {
            return null;
        }
    }

    return $value;
}
