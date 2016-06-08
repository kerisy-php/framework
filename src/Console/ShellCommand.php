<?php
/**
 * Kerisy Framework
 * 
 * PHP Version 7
 * 
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Console
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Console;

use Kerisy\Core\Console\Command;
use Kerisy\Core\InvalidParamException;
use Kerisy\Core\InvalidValueException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class ShellCommand
 *
 * @package Kerisy\Console
 */
class ShellCommand extends Command
{
    public $name = 'shell';
    public $description = 'Kerisy shell management';

    protected function configure()
    {
        $this->addArgument('operation', InputArgument::OPTIONAL, 'the operation:run');
        $this->addOption('router', '-r', InputArgument::OPTIONAL , 'define operation router crond path');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument('operation') ? $input->getArgument('operation') : 'run';
        $router = $input->getOption('router');
        if (!in_array($operation, ['run'])) {
            throw new InvalidParamException('The <operation> argument is invalid');
        }

        return call_user_func([$this, 'command' . $operation], $router);

    }

    protected function commandRun($router)
    {
        return app()->makeExecuor($router);
    }

}
