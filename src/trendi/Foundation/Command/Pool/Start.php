<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Pool;

use Trendi\Console\Input\InputInterface;
use Trendi\Console\Input\InputOption;
use Trendi\Console\Output\OutputInterface;
use Trendi\Foundation\Command\Base;

class Start extends Base
{
    protected function configure()
    {
        $this->setName('pool:start')
            ->setDescription('start the pool server ');
        $this->addOption('--daemonize', '-d', InputOption::VALUE_NONE, 'Is daemonize ?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        PoolBase::operate("start", $output, $input);
    }
}
