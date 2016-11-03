<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Kerisy\Foundation\Command\Pool;

use Kerisy\Console\Input\InputInterface;
use Kerisy\Console\Output\OutputInterface;
use Kerisy\Foundation\Command\Base;

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
