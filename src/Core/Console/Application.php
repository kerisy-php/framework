<?php

namespace Kerisy\Core\Console;

use Kerisy\Core\Configurable;
use Kerisy\Core\ObjectTrait;
use Symfony\Component\Console\Application as SymfonyConsole;

/**
 * Class Application
 *
 * @package Kerisy\Core\Console
 */
class Application extends SymfonyConsole implements Configurable
{
    use ObjectTrait;

    public $name = 'UNKNOWN';
    public $version = 'UNKNOWN';
    public $kerisy;

    public function __construct($config = [])
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }

        parent::__construct($this->name, $this->version);

        $this->init();
    }
}
