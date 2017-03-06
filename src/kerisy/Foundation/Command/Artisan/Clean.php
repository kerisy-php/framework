<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Foundation\Command\Artisan;

use Kerisy\Console\Input\InputInterface;
use Kerisy\Console\Input\InputOption;
use Kerisy\Console\Output\OutputInterface;
use Kerisy\Console\Input\InputArgument;
use Kerisy\Foundation\Command\Base;
use Kerisy\Support\Log;

class Clean extends Base
{
    protected function configure()
    {
        $this->setName('clean')
            ->setDescription('clean project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
    }
}
