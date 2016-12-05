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

namespace Kerisy\Foundation\Command\Server;

use Kerisy\Console\Input\InputInterface;
use Kerisy\Console\Input\InputOption;
use Kerisy\Console\Input\InputArgument;
use Kerisy\Console\Output\OutputInterface;
use Kerisy\Foundation\Command\Base;

class Restart extends Base
{
    protected function configure()
    {
        $this
            ->setName('server:restart')
            ->setDescription('restart the all server');
        $this->addOption('--daemonize', '-d', InputOption::VALUE_NONE, 'Is daemonize ?');
        $this->addOption('--option', '-o', InputOption::VALUE_OPTIONAL, 'diy server option ?');
    }

    protected function execute(InputInterface $input,OutputInterface $output)
    {
        ServerBase::operate("restart", $this, $input);
    }
}
