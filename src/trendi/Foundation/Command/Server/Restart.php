<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Server;

use Trendi\Console\Command\Command;
use Trendi\Console\Input\InputInterface;
use Trendi\Console\Input\InputOption;
use Trendi\Console\Output\OutputInterface;
use Trendi\Foundation\Command\Base;

class Restart extends Base
{
    protected function configure()
    {
        $this
            ->setName('server:restart')
            ->setDescription('restart the all server');
        $this->addOption('--daemonize', '-d', InputOption::VALUE_NONE, 'Is daemonize ?');
    }

    protected function execute(InputInterface $input,OutputInterface $output)
    {
        ServerBase::operate("restart", $output, $input);
    }
}
