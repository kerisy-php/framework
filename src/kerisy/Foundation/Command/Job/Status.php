<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Kerisy\Foundation\Command\Job;

use Kerisy\Console\Input\InputInterface;
use Kerisy\Console\Output\OutputInterface;
use Kerisy\Foundation\Command\Base;

class Status extends Base
{
    protected function configure()
    {
        $this
            ->setName('job:status')
            ->setDescription('show job server status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        JobBase::operate("status", $output, $input);
    }
}
