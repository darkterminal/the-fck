<?php

namespace Fckin\core;

class View
{
    public string $title = '';

    public function renderView($view, $params = [])
    {
        $viewContent = $this->renderOnlyView($view, $params);
        $layoutContent = $this->layoutContent($params);
        return \str_ireplace('{{content}}', $viewContent, $layoutContent);
    }

    protected function layoutContent($params = [])
    {
        $layout = Application::$app->layout;
        if (Application::$app->controller) {
            $layout = Application::$app->controller->layout;
        }
        foreach ($params as $key => $val) {
            $$key = $val;
        }
        ob_start();
        include_once Application::$ROOT_DIR . "/views/layouts/$layout.php";
        return ob_get_clean();
    }

    protected function renderOnlyView($view, $params)
    {
        foreach ($params as $key => $val) {
            $$key = $val;
        }
        ob_start();
        include_once Application::$ROOT_DIR . "/views/$view.php";
        return ob_get_clean();
    }

    public function renderComponent(string $view, array $params = [])
    {
        foreach ($params as $key => $val) {
            $$key = $val;
        }
        ob_start();
        include_once Application::$ROOT_DIR . "/views/$view.php";
        return ob_get_clean();
    }

    public function addComponent(string $view, array $params = [])
    {
        foreach ($params as $key => $val) {
            $$key = $val;
        }
        ob_start();
        include_once Application::$ROOT_DIR . "/views/components/$view.php";
        return ob_get_clean();
    }

    public function addPage(string $view, array $params = [])
    {
        foreach ($params as $key => $val) {
            $$key = $val;
        }
        ob_start();
        include_once Application::$ROOT_DIR . "/views/pages/$view.php";
        return ob_get_clean();
    }
}
