<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Server;

use Trendi\Console\Input\InputInterface;
use Trendi\Console\Output\OutputInterface;
use Trendi\Foundation\Command\Base;

class Status extends Base
{
    protected function configure()
    {
        $this
            ->setName('server:status')
            ->setDescription('show all server status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ServerBase::operate("status", $output, $input);
    }
}
