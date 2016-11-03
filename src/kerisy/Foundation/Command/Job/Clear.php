<?php
/**
 * Created by PhpStorm.
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Foundation\Command\Job;

use Kerisy\Console\Input\InputInterface;
use Kerisy\Console\Output\OutputInterface;
use Kerisy\Foundation\Command\Base;

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
