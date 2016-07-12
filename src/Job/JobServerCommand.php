<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/5/31
 */

namespace Kerisy\Job;

use Kerisy\Core\Console\Command;
use Kerisy\Core\InvalidParamException;
use Kerisy\Core\InvalidValueException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JobServerCommand extends Command{
    public $name = 'jobserver';
    public $description = 'Kerisy jobserver management';

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
        $options = [];
        $options['asDaemon'] = 0;
        \Kerisy\Job\Worker::run($options);
    }

    protected function handleStart()
    {
        $pidFile = APPLICATION_PATH . '/runtime/jobserver.pid';

        if(!is_dir(dirname($pidFile))){
            throw new InvalidValueException('The runtime dir not exists');
        }

        if (file_exists($pidFile)) {
            throw new InvalidValueException('The pidfile exists, it seems the server is already started');
        }
        $server = [];
        $server['asDaemon'] = 1;
        $server['pidFile'] = $pidFile;
        \Kerisy\Job\Worker::run($server);
    }

    protected function handleRestart()
    {
        $this->handleStop();

        return $this->handleStart();
    }

    protected function handleStop()
    {
        $pidFile = APPLICATION_PATH . '/runtime/jobserver.pid';
        if (file_exists($pidFile)) {
            $pids = explode("|",file_get_contents($pidFile));
            $del = 1;
            foreach ($pids as $pid){
                $rs = posix_kill($pid, 15);
                if(!$rs){
                    $del = 0;
                    echo "del_fail\r\n";
                    print_r(posix_get_last_error());
                    echo "\r\n";
                }
            }
            if($del){
                echo "del_ok\r\n";
                print_r(posix_get_last_error());
                echo "\r\n";
                do {
                    unlink($pidFile);
                    usleep(100000);
                } while(file_exists($pidFile));
                return 0;
            }

        }

        return 1;
    }

}