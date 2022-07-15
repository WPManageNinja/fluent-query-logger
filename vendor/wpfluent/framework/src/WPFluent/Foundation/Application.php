<?php

namespace FluentQueryLogger\Framework\Foundation;

use InvalidArgumentException;
use FluentQueryLogger\Framework\Foundation\Config;
use FluentQueryLogger\Framework\Foundation\Container;
use FluentQueryLogger\Framework\Foundation\ComponentBinder;
use FluentQueryLogger\Framework\Foundation\FoundationTrait;
use FluentQueryLogger\Framework\Foundation\AsyncRequestTrait;
use FluentQueryLogger\Framework\Foundation\CronTaskSchedulerTrait;

class Application extends Container
{
    use FoundationTrait;
    use AsyncRequestTrait;
    use CronTaskSchedulerTrait;

    protected $file = null;
    protected $baseUrl = null;
    protected $basePath = null;
    protected $handlerNamespace = null;
    protected $controllerNamespace = null;
    protected $permissionNamespace = null;

    public function __construct($file = null)
    {
        $this->init($file);
        $this->setAppLevelNamespace();
        $this->bootstrapApplication();
    }

    protected function init($file)
    {
        $this->file = $this->pluginFilePath($file);
        $this->basePath = plugin_dir_path($this->file);
        $this->baseUrl = plugin_dir_url($this->file);
    }

    protected function pluginFilePath($file)
    {
        $file = $file ?: realpath(__DIR__ . '/../../../fluent-query-logger.php');
        
        return $file;
    }

    protected function setAppLevelNamespace()
    {
        $autoload = $this->getComposer('autoload');

        $psr4 = array_flip($autoload['psr-4']);

        $this->handlerNamespace = $psr4['app/'] . 'Hooks\Handlers';

        $this->policyNamespace = $psr4['app/'] . 'Http\Policies';
        
        $this->controllerNamespace = $psr4['app/'] . 'Http\Controllers';
    }

    protected function getComposer($section = null)
    {
        $data = json_decode(
            file_get_contents($this->basePath . 'composer.json'), true
        );

        return $section ? $data[$section] : $data;
    }

    protected function bootstrapApplication()
    {
        $this->bindAppInstance();
        $this->bindPathsAndUrls();
        $this->loadConfigIfExists();
        $this->loadTextdomain();
        $this->bindComponents($this);
        $this->requireCommonFiles($this);
    }

    protected function bindAppInstance()
    {
        App::setInstance($this);
        $this->instance('app', $this);
        $this->instance(__CLASS__, $this);
    }

    protected function bindPathsAndUrls()
    {
        $this->bindUrls();
        $this->basePaths();
    }

    protected function bindUrls()
    {
        $this['url.assets'] = $this->baseUrl . 'assets/';
    }

    protected function basePaths()
    {
        $this['path'] = $this->basePath;
        $this['path.app'] = $this->basePath . 'app/';
        $this['path.hooks'] = $this['path.app'] . 'Hooks/';
        $this['path.http'] = $this['path.app'] . 'Http/';
        $this['path.controllers'] = $this['path.http'] . 'Controllers/';
        $this['path.config'] = $this->basePath . 'config/';
        $this['path.assets'] = $this->basePath . 'assets/';
        $this['path.resources'] = $this->basePath . 'resources/';
        $this['path.views'] = $this['path.app'] . 'Views/';
    }

    protected function loadConfigIfExists()
    {
        $data = [];

        if (is_dir($this['path.config'])) {
            foreach (glob($this['path.config'] . '*.php') as $file) {
                $data[basename($file, '.php')] = require_once($file);
            }
        }

        $this->instance('config', new Config($data));
    }

    protected function loadTextdomain()
    {
        $this->addAction('init', function() {
            load_plugin_textdomain(
                $this->config->get('app.text_domain'), false, $this->textDomainPath()
            );
        });
    }

    protected function textDomainPath()
    {
        return basename($this['path']) . $this->config->get('app.domain_path');
    }

    protected function bindComponents($app)
    {
        (new ComponentBinder($app))->bindComponents();
    }

    protected function requireCommonFiles($app)
    {
        require_once $this->basePath . 'app/Hooks/actions.php';
        require_once $this->basePath . 'app/Hooks/filters.php';

        if (file_exists($includes = $this->basePath . 'app/Hooks/includes.php')) {
            require_once $includes;
        }

        $this->registerAsyncActions();

        $this->addAction('rest_api_init', function($wpRestServer) use ($app) {
            try {
                $router = $app->router;
                require_once $this->basePath . 'app/Http/Routes/api.php';
                $router->registerRoutes();
            } catch (InvalidArgumentException $e) {
                return $app->response->json([
                    'message' => $e->getMessage()
                ], $e->getCode() ?: 500);
            }
        });
    }
}
