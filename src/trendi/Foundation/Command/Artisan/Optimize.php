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
