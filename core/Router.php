<?php

namespace Fckin\core;

use Exception;
use Fckin\core\exceptions\NotFoundException;

class Router
{
    public Request $request;
    public Response $response;
    protected array $routes = [];
    protected array $routeGroups = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get($path, $callback)
    {
        $this->addGroupRoute('get', $path, $callback);
    }

    public function post($path, $callback)
    {
        $this->addGroupRoute('post', $path, $callback);
    }

    public function put($path, $callback)
    {
        $this->addGroupRoute('put', $path, $callback);
    }

    public function patch($path, $callback)
    {
        $this->addGroupRoute('patch', $path, $callback);
    }

    public function delete($path, $callback)
    {
        $this->addGroupRoute('delete', $path, $callback);
    }

    protected function addRoute($method, $path, $callback)
    {
        $this->routes[$method][$path] = $callback;
    }

    public function group(string $prefix, callable $callback)
    {
        $this->routeGroups[] = $prefix;
        call_user_func($callback, $this);
        array_pop($this->routeGroups);
    }

    public function match(array $methods, $path, $callback)
    {
        foreach ($methods as $method) {
            $this->addGroupRoute(strtolower($method), $path, $callback);
        }
    }

    protected function addGroupRoute($method, $path, $callback)
    {
        $prefix = implode('', $this->routeGroups);
        $path = rtrim($prefix, '/') . '/' . ltrim($path, '/');
        $this->addRoute($method, $path, $callback);
    }

    public function resolve()
    {
        $method = strtolower($this->request->method());

        foreach ($this->routes[$method] as $route => $callback) {
            if ($this->matchRoute($route)) {
                $params = $this->getParams($route);
                return $this->handleCallback($callback, $params);
            }
        }

        return $this->notFoundResponse();
    }

    protected function matchRoute($route)
    {
        $path = $this->request->getPath();
        $routeSegments = explode('/', trim($route, '/'));
        $pathSegments = explode('/', trim($path, '/'));

        if (count($routeSegments) !== count($pathSegments)) {
            return false;
        }

        foreach ($routeSegments as $key => $segment) {
            if (!isset($pathSegments[$key])) {
                return false;
            }

            if ($segment !== $pathSegments[$key] && !preg_match('/^\{(.+?)\}$/', $segment)) {
                return false;
            }
        }

        return true;
    }

    protected function getParams($route)
    {
        $path = $this->request->getPath();
        $routeSegments = explode('/', trim($route, '/'));
        $pathSegments = explode('/', trim($path, '/'));

        $params = [];

        foreach ($routeSegments as $key => $segment) {
            if ($segment === '' || !isset($pathSegments[$key])) {
                return false;
            }

            if (preg_match('/^\{(.+?)\}$/', $segment, $matches)) {
                $params[$matches[1]] = $pathSegments[$key];
            } elseif (preg_match('/^\[{(.+?)\}\]$/', $segment, $matches)) {
                $params[$matches[1]] = $pathSegments[$key] ?? null;
            } elseif ($pathSegments[$key] !== $segment) {
                return false;
            }
        }

        return $this->request->params = $params;
    }

    protected function handleCallback($callback, $params = [])
    {
        if (is_callable($callback)) {
            return call_user_func_array($callback, array_merge([$params, $this->request, $this->response]));
        } elseif (is_string($callback)) {
            return $this->callControllerMethod($callback, $params);
        }

        return null;
    }

    protected function callControllerMethod($callback, $params = [])
    {
        [$controllerName, $method] = explode('@', $callback);
        $controller = $this->instantiateController($controllerName);

        Application::$app->controller = $controller;
        Application::$app->controller->action = $method;

        foreach (Application::$app->controller->getMiddleware() as $middleware) {
            $middleware->execute();
        }

        return call_user_func([$controller, $method], $this->request, $this->response);
    }

    protected function instantiateController($controllerName)
    {
        $composerFile = Application::$ROOT_DIR . DIRECTORY_SEPARATOR . 'composer.json';

        if (!file_exists($composerFile)) {
            $message = "You don't have composer.json!";
            \logger(type: 'critical', message: $message);
            throw new Exception($message, 1);
        }

        $composer = json_decode(file_get_contents($composerFile), true);
        $controllerClass = '\\' . key($composer['autoload']['psr-4']) . 'controllers\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            $message = "Controller class $controllerClass not found";
            \logger(type: 'critical', message: $message);
            throw new Exception($message, 1);
        }

        return new $controllerClass();
    }

    protected function notFoundResponse()
    {
        $this->response->setStatusCode(404);
        throw new NotFoundException();
    }
}
