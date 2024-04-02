<?php

use Fckin\core\Application;
use Fckin\core\FTAuth;
use Fckin\core\Response;
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

function config($configFile, $key)
{
    $configPath = Application::$ROOT_DIR . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $configFile . '.php';

    if (!file_exists($configPath)) {
        return $key . " doesn't exist!";
    }

    $config = require $configPath;

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

function base_url(string $path = ''): string
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

    $host = $_SERVER['HTTP_HOST'];

    $basePath = dirname($_SERVER['SCRIPT_NAME']);

    $baseUrl = "$protocol://$host$basePath";

    if ($path !== '') {
        $baseUrl .= ltrim($path, '/');
    }

    return $baseUrl;
}

function getParams(): array
{
    return Application::$app->request->getParams();
}

function getParam(string $key): mixed
{
    return Application::$app->request->getParam($key);
}

function loadJsonFile(string $path): array
{
    $dir = dirname(dirname(__DIR__));
    $data = \json_decode(\file_get_contents("$dir/$path"), true);
    return $data;
}

function isActiveLink(string $baseUrl): bool
{
    $currentURL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $currentURL === $baseUrl;
}

function createBreadcrumbs(): string
{
    $currentURL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $path = parse_url($currentURL, PHP_URL_PATH);

    $pathParts = explode('/', trim($path, '/'));

    $breadcrumbs = array();

    $breadcrumbs[] = '<li><a href="/">Home</a></li>';

    $currentPath = '/';
    $totalPaths = \count($pathParts);
    $no = 1;
    foreach ($pathParts as $part) {

        $part = ucfirst($part);


        $part = str_replace('-', ' ', $part);


        $currentPath .= \strtolower($part) . '/';


        $breadcrumbs[] = '<li><a href="' . ($totalPaths === $no ? '#' : \rtrim($currentPath, '/')) . '" ' . $totalPaths === $no ? 'class="disabled"' : '' . '>' . $part . '</a></li>';
        $no++;
    }

    $breadcrumbsString = implode('', $breadcrumbs);

    return '<div class="text-sm breadcrumbs"><ul>' . $breadcrumbsString . '</ul></div>';
}

function remove_keys(array|object $data, array $exclude): array|object
{
    $data = (array) $data;

    foreach ($exclude as $key) {
        unset($data[$key]);
    }

    foreach ($data as &$value) {
        if (is_array($value) || is_object($value)) {
            $value = self::remove_keys($value, $exclude);
        }
    }

    return $data;
}

function buildHxAttributes($base_url, $queryString, $column, $activeColumn, $targetId, $search = '', $extraClasses = '')
{
    $hxAttributes = 'hx-get="' . base_url($base_url . '?' . $queryString . '&column=' . $column . '&search=' . $search) . '" ';
    $hxAttributes .= 'hx-swap="outerHTML" ';
    $hxAttributes .= 'hx-target="#' . $targetId . '" ';
    $hxAttributes .= 'hx-indicator="#table-indicator" ';
    $hxAttributes .= 'class="cursor-pointer hover:bg-base-300';
    if ($activeColumn === $column) {
        $hxAttributes .= ' bg-base-300 ';
    }
    $hxAttributes .= $extraClasses . '"';

    return $hxAttributes;
}

function hxPagination($base_url, $queryString, $targetId)
{
    $hxAttributes = 'hx-get="' . base_url($base_url . '?' . $queryString) . '" ';
    $hxAttributes .= 'hx-swap="outerHTML" ';
    $hxAttributes .= 'hx-target="#' . $targetId . '" ';
    $hxAttributes .= 'hx-indicator="#table-indicator" ';

    return $hxAttributes;
}

function handleAjaxOrRedirect(Response $response, string $path, int $code)
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        $response->setStatusCode($code);
    } else {
        $response->redirect($path);
    }
    exit();
}

function isAjax()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function toObject($array)
{
    if (!is_array($array)) {
        return $array;
    }

    $object = new \stdClass();

    foreach ($array as $key => $value) {
        $object->$key = self::toObject($value);
    }

    return $object;
}

function getValidationError(array|null $errors, string $key)
{
    if (!empty($errors) && array_key_exists($key, $errors)) {
        $errorMessage = implode(', ', $errors[$key]);
        return '<span class="text-sm text-red-500">' . $errorMessage . '</span>';
    }

    return null;
}

function collection(string $name)
{
    $className = "App\models\\" . $name;
    return new $className();
}
