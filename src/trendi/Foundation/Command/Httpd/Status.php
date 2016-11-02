<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Httpd;

use Trendi\Console\Input\InputInterface;
use Trendi\Console\Output\OutputInterface;
use Trendi\Foundation\Command\Base;

class Status extends Base
{
    protected function configure()
    {
        $this
            ->setName('httpd:status')
            ->setDescription('show http server status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        HttpdBase::operate("status", $output, $input);
    }
}
