<?php
/**
 *  监控server 命令入口
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/5/31
 */

namespace Kerisy\Monitor\Console;

use Kerisy\Core\Console\Command;
use Kerisy\Core\InvalidParamException;
use Kerisy\Core\InvalidValueException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MonitorServerCommand extends Command{
    
    // 命令名称
    public $name = 'monitorserver';
    
    //命令描述
    public $description = 'Kerisy monitor-server management';

    /**
     *  命令参数配置
     */
    protected function configure()
    {
        $this->addArgument('operation', InputArgument::REQUIRED, 'the operation: run, start, restart or stop');
    }

    /**
     * 命令执行的操作
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument('operation');

        if (!in_array($operation, ['run',"stop","start","restart"])) {
            throw new InvalidParamException('The <operation> argument is invalid');
        }

        return call_user_func([$this, 'handle' . $operation]);

    }

    /**
     * monitorserver run 命令
     * 
     * @return mixed
     * @throws \Kerisy\Core\InvalidConfigException
     */
    protected function handleRun()
    {
        $server = config('monitorservice')->all();
        $server['asDaemon'] = 0;
        
        return make($server)->run();
    }

    /**
     *   monitorserver start 命令
     * 
     * @return mixed
     * @throws \Kerisy\Core\InvalidConfigException
     */
    protected function handleStart()
    {
        $pidFile = APPLICATION_PATH . '/runtime/monitorserver.pid';

        if (file_exists($pidFile)) {
            throw new InvalidValueException('The pidfile exists, it seems the server is already started');
        }

        $server = config('monitorservice')->all();
        $server['asDaemon'] = 1;
        $server['pidFile'] = APPLICATION_PATH . '/runtime/monitorserver.pid';

        return make($server)->run();
    }

    /**
     *  monitorserver restart 命令
     * 
     * @return mixed
     */
    protected function handleRestart()
    {
        $this->handleStop();

        return $this->handleStart();
    }

    /**
     *  monitorserver stop 命令
     * 
     * @return int
     */
    protected function handleStop()
    {
        $pidFile = APPLICATION_PATH . '/runtime/monitorserver.pid';
        if (file_exists($pidFile) && posix_kill(file_get_contents($pidFile), 15)) {
            do {
                usleep(100000);
            } while(file_exists($pidFile));
            return 0;
        }

        return 1;
    }

}