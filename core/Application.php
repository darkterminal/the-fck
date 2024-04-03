<?php

namespace Fckin\core;

use Fckin\core\db\Database;

class Application
{
    public static string $ROOT_DIR;
    public static Application $app;

    public string $layout = 'default';
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;
    public Database $db;
    public View $view;
    public ?Controller $controller = null;

    public function __construct($rootPath, array $config)
    {
        self::$app = $this;
        self::$ROOT_DIR = $rootPath;
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->view = new View();
        $this->db = new Database($config['db']);
        $this->router = new Router($this->request, $this->response);
    }

    public function getController(): Controller
    {
        return $this->controller;
    }

    public function setController(Controller $controller): void
    {
        $this->controller = $controller;
    }

    public function run()
    {
        $run = new \Whoops\Run;
        $handler = new \Whoops\Handler\PrettyPageHandler();
        $handler->setApplicationPaths([self::$ROOT_DIR]);
        $handler->setEditor('vscode');
        $run->pushHandler($handler);

        $run->pushHandler(function ($exception, $inspector, $run) {

            $inspector->getFrames()->map(function ($frame) {

                if ($function = $frame->getFunction()) {
                    $frame->addComment("This frame is within function '$function'", 'cpt-obvious');
                }

                return $frame;
            });
        });

        $run->register();

        whoops_add_stack_frame(function () {
            echo $this->router->resolve();
        });
    }
}
