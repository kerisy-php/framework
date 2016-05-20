<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Core
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Core;

use Kerisy;
use Kerisy\Di\Container;
use Kerisy\Log\Logger;
use Kerisy\Http\Request;
use Kerisy\Http\Response;
use Kerisy\Database\Database;

/**
 * Class Application
 *
 * @package Kerisy\Http
 */
class Application extends ServiceLocator
{
    const VERSION = '2.0.0';

    /**
     * The name for the application.
     *
     * @var string
     */
    public $name = 'Kerisy';

    /**
     * Available console commands.
     *
     * @var string[]
     */
    public $commands = [];

    public $modules = [];

    /**
     * Application component definitions.
     *
     * @var array
     */
    public $components = [];

    public $debug = true;

    /**
     * The environment that the application is running on. dev, prod or test.
     *
     * @var string
     */
    public $environment = 'development';

    public $timezone = 'UTC';

    public $runtime;

    protected $dispatcher;
    protected $bootstrapped = false;
    protected $refreshing = [];

    private $_configs;

    public function __construct()
    {
        parent::__construct($this->config('application')->all());
    }

    public function init()
    {
        if (!defined('APPLICATION_PATH') || !file_exists(APPLICATION_PATH)) {
            throw new InvalidParamException("The param: 'APPLICATION_PATH' is invalid");
        }

        $this->components = array_merge($this->defaultComponents(), $this->config('components')->all());

        Container::getInstance()->setApp($this);

        Kerisy::$app = $this;
    }


    protected function initializeConfig()
    {
        date_default_timezone_set($this->timezone);
    }

    /*
        protected function registerEntities()
        {
            $config = $this->config('database')->all();

            return new Database($config['connections'][$config['default']]);
        }
    */

    protected function registerComponents()
    {
        foreach ($this->components as $id => $definition) {
            $this->bind($id, $definition);
        }

        foreach ($this->components as $id => $_) {
            if ($this->get($id) instanceof ShouldBeRefreshed) {
                $this->refreshing[$id] = true;
            }
        }
    }

    public function config($config_group)
    {
        if (!isset($this->_configs[$config_group])) {
            $config = new Config($config_group);
            $this->_configs[$config_group] = $config;
        }

        return $this->_configs[$config_group];
    }


    public function handleConsole($input, $output)
    {
        $app = new \Kerisy\Core\Console\Application([
            'name' => 'Kerisy Command Runner',
            'version' => self::VERSION,
            'kerisy' => $this,
        ]);

        $commands = array_merge($this->commands, [
            'Kerisy\Console\ServerCommand',
            'Kerisy\Console\ShellCommand'
        ]);

        foreach ($commands as $command) {
            $app->add(make(['class' => $command, 'kerisy' => $this]));
        }

        return $app->run($input, $output);
    }



    public function defaultComponents()
    {
        return [
            'errorHandler' => [
                'class' => ErrorHandler::class,
            ],
            'log' => [
                'class' => Logger::class,
            ],
            'request' => [
                'class' => Request::class,
            ],
            'response' => [
                'class' => Response::class,
            ],
        ];
    }

    /**
     * Shutdown the application.
     */
    public function shutdown()
    {

    }
}
