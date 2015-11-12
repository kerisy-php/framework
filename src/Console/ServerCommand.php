<?php

namespace Kerisy\Console;

use Kerisy\Core\Console\Command;
use Kerisy\Core\InvalidParamException;
use Kerisy\Core\InvalidValueException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class ServerCommand
 *
 * @package Kerisy\Console
 */
class ServerCommand extends Command
{
    public $name = 'server';
    public $description = 'Kerisy server management';

    protected function configure()
    {
        $this->addArgument('operation', InputArgument::REQUIRED, 'the operation: serve, start, restart or stop');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument('operation');

        if (!in_array($operation, ['run', 'start', 'restart', 'stop'])) {
            throw new InvalidParamException('The <operation> argument is invalid');
        }

        return call_user_func([$this, 'handle' . $operation]);

    }

    protected function handleRun()
    {
        $server = require CONFIG_PATH . 'server.php';
        $server['asDaemon'] = 0;

        return make($server)->run();
    }

    protected function handleStart()
    {
        $pidFile = APPLICATION_PATH . 'runtime/server.pid';

        if (file_exists($pidFile)) {
            throw new InvalidValueException('The pidfile exists, it seems the server is already started');
        }

        $server = require CONFIG_PATH . 'server.php';
        $server['asDaemon'] = 1;
        $server['pidFile'] = APPLICATION_PATH . 'runtime/server.pid';

        return make($server)->run();
    }

    protected function handleRestart()
    {
        $this->handleStop();

        return $this->handleStart();
    }

    protected function handleStop()
    {
        $pidFile = APPLICATION_PATH . 'runtime/server.pid';
        if (file_exists($pidFile) && posix_kill(file_get_contents($pidFile), 15)) {
            do {
                usleep(100000);
            } while(file_exists($pidFile));
            return 0;
        }

        return 1;
    }
}
