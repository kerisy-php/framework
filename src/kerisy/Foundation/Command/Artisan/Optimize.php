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

namespace Kerisy\Foundation\Command\Artisan;

use Kerisy\Console\Input\InputInterface;
use Kerisy\Console\Input\InputOption;
use Kerisy\Console\Output\OutputInterface;
use Kerisy\Console\Input\InputArgument;
use Kerisy\Foundation\Command\Base;
use Kerisy\Support\Log;

class Optimize extends Base
{
    protected function configure()
    {
        $this->setName('optimize')
            ->setDescription('optimize project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->checkCmd("composer")){
            $cmdStr = "composer dump-autoload --optimize";
            exec($cmdStr);
            Log::sysinfo(" 'composer dump-autoload --optimize' run success! ");
        }
    }

    protected function checkCmd($cmd)
    {
        $cmdStr = "command -v ".$cmd;
        exec($cmdStr, $check);
        if(!$check){
            return false;
        }else{
            return current($check);
        }
    }

}
