<?php

namespace Fckin\core;

use Dotenv\Dotenv;
use Exception;
use Fckin\core\Application;

final class Fck
{
    private string $appDir;

    public function __construct(string $appDir)
    {
        $this->appDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $appDir);
    }

    public function boot()
    {
        $configPath = $this->appDir . '/config/app.php';
        $routerPath = $this->appDir . '/routes/web.php';

        if (!file_exists($configPath) || !file_exists($routerPath)) {
            $message = "Error in the fck configuration";
            \logger(type: 'critical', message: $message);
            throw new Exception($message, 1);
        }

        $dotenv = Dotenv::createImmutable($this->appDir);
        $dotenv->load();

        $config = require_once $this->appDir . '/config/app.php';

        $app = new Application($this->appDir, $config);

        require_once $this->appDir . '/routes/web.php';

        $app->run();
    }
}
