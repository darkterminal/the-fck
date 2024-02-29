<?php

namespace Fckin\core;

use Exception;
use Fckin\core\exceptions\NotFoundException;

class Router
{
    public Request $request;
    public Response $response;
    protected array $routes = [];
    protected string $routePrefix = '';

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get($path, $callback)
    {
        $this->addRoute('get', $path, $callback);
    }

    public function post($path, $callback)
    {
        $this->addRoute('post', $path, $callback);
    }

    public function put($path, $callback)
    {
        $this->addRoute('put', $path, $callback);
    }

    public function delete($path, $callback)
    {
        $this->addRoute('delete', $path, $callback);
    }

    protected function addRoute($method, $path, $callback)
    {
        $this->routes[$method][$path] = $callback;
    }

    public function resolve()
    {
        $method = strtolower($this->request->method());

        foreach ($this->routes[$method] as $route => $callback) {
            $params = $this->getParams($route);

            if ($params !== false) {
                return $this->handleCallback($callback, $params);
            }
        }

        return $this->notFoundResponse();
    }

    protected function getParams($route)
    {
        $path = $this->request->getPath();
        $routeSegments = explode('/', trim($route, '/'));
        $pathSegments = explode('/', trim($path, '/'));

        foreach ($routeSegments as $key => $segment) {
            if (preg_match('/^\{(.+?)\}$/', $segment, $matches)) {
                if (isset($pathSegments[$key])) {
                    $this->request->params[$matches[1]] = $pathSegments[$key];
                } else {
                    return false;
                }
            } elseif (preg_match('/^\[{(.+?)\}\]$/', $segment, $matches)) {
                if (isset($pathSegments[$key])) {
                    $this->request->params[$matches[1]] = $pathSegments[$key];
                } else {
                    $this->request->params[$matches[1]] = null;
                }
            } else {
                if (!isset($pathSegments[$key]) || $pathSegments[$key] !== $segment) {
                    return false;
                }
            }
        }

        return $this->request->params;
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
            throw new Exception("You don't have composer.json!", 1);
        }

        $composer = json_decode(file_get_contents($composerFile), true);
        $controllerClass = '\\' . key($composer['autoload']['psr-4']) . 'controllers\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            throw new Exception("Controller class $controllerClass not found", 1);
        }

        return new $controllerClass();
    }

    protected function notFoundResponse()
    {
        $this->response->setStatusCode(404);
        throw new NotFoundException();
    }
}
