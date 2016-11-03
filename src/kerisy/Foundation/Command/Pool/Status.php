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

class Status extends Base
{
    protected function configure()
    {
        $this
            ->setName('pool:status')
            ->setDescription('show pool server status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        PoolBase::operate("status", $output, $input);
    }
}
