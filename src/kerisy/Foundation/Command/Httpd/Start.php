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

namespace Kerisy\Foundation\Command\Httpd;

use Kerisy\Console\Input\InputInterface;
use Kerisy\Console\Input\InputOption;
use Kerisy\Console\Output\OutputInterface;
use Kerisy\Foundation\Command\Base;

class Start extends Base
{
    protected function configure()
    {
        $this->setName('httpd:start')
            ->setDescription('start the http server ');
        $this->addOption('--daemonize', '-d', InputOption::VALUE_NONE, 'Is daemonize ?');
        $this->addOption('--usefis', '-u', InputOption::VALUE_NONE, 'use fis ?');
        $this->addOption('--releasefis', '-r', InputOption::VALUE_OPTIONAL, 'release fis ?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        HttpdBase::operate("start", $output, $input);
    }
}
