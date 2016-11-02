<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Artisan;

use Trendi\Console\Input\InputInterface;
use Trendi\Console\Input\InputOption;
use Trendi\Console\Output\OutputInterface;
use Trendi\Console\Input\InputArgument;
use Trendi\Foundation\Command\Base;
use Trendi\Support\Log;

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
