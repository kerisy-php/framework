<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/5/31
 */

namespace Kerisy\Rpc\Console;

use Kerisy\Core\Console\Command;
use Kerisy\Core\InvalidParamException;
use Kerisy\Core\InvalidValueException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RpcServerCommand extends Command{
    public $name = 'rpcserver';
    public $description = 'Kerisy rpc-server management';

    protected function configure()
    {
        $this->addArgument('operation', InputArgument::REQUIRED, 'the operation: run, start, restart or stop');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument('operation');

        if (!in_array($operation, ['run',"stop","start","restart"])) {
            throw new InvalidParamException('The <operation> argument is invalid');
        }

        return call_user_func([$this, 'handle' . $operation]);

    }

    protected function handleRun()
    {
        $server = config('rpcservice')->all();
        $server['asDaemon'] = 0;
        
        return make($server)->run();
    }

    protected function handleStart()
    {
        $pidFile = APPLICATION_PATH . '/runtime/rpcserver.pid';

        if (file_exists($pidFile)) {
            throw new InvalidValueException('The pidfile exists, it seems the server is already started');
        }

        $server = config('rpcservice')->all();
        $server['asDaemon'] = 1;
        $server['pidFile'] = APPLICATION_PATH . '/runtime/rpcserver.pid';

        return make($server)->run();
    }

    protected function handleRestart()
    {
        $this->handleStop();

        return $this->handleStart();
    }

    protected function handleStop()
    {
        $pidFile = APPLICATION_PATH . '/runtime/rpcserver.pid';
        if (file_exists($pidFile) && posix_kill(file_get_contents($pidFile), 15)) {
//            echo "del_ok\r\n";
//            print_r(posix_get_last_error());
//            echo "\r\n";
            do {
                usleep(100000);
            } while(file_exists($pidFile));
            return 0;
        }

        return 1;
    }

}