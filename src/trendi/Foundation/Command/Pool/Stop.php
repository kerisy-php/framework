<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Pool;

use Trendi\Console\Input\InputInterface;
use Trendi\Console\Output\OutputInterface;
use Trendi\Foundation\Command\Base;

class Stop extends Base
{
    protected function configure()
    {
        $this
            ->setName('pool:stop')
            ->setDescription('stop the pool server ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        PoolBase::operate("stop", $output, $input);
    }
}
