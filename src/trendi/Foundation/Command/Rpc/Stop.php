<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Rpc;

use Trendi\Console\Input\InputInterface;
use Trendi\Console\Output\OutputInterface;
use Trendi\Foundation\Command\Base;

class Stop extends Base
{
    protected function configure()
    {
        $this
            ->setName('rpc:stop')
            ->setDescription('stop the rpc server ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        RpcBase::operate("stop", $output, $input);
    }
}
