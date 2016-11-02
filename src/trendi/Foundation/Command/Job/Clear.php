<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Job;

use Trendi\Console\Input\InputInterface;
use Trendi\Console\Output\OutputInterface;
use Trendi\Foundation\Command\Base;

class Clear extends Base
{
    protected function configure()
    {
        $this
            ->setName('job:clear')
            ->setDescription('clear the job data ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        JobBase::operate("clear", $output, $input);
    }
}
