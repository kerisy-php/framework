<?php
/**
 * User: wangkaihui
 * Date: 16/7/28
 * Time: 下午1:34
 */

namespace Kerisy\Console;

use Kerisy\Core\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class PHPCsCommand extends Command
{
    public $name = 'phpcs';
    public $description = 'PHP Codesniffer Tool';

    protected function configure()
    {
        $this->addOption('--report', '-r', InputOption::VALUE_REQUIRED, "set report format");
        $this->addOption('--file', '-f', InputOption::VALUE_REQUIRED, "set report output file path");
        $this->addOption('--cbf', '-c', InputOption::VALUE_REQUIRED, "PHPCBF FIX");
        $this->addOption('--ignore', '-i', InputOption::VALUE_REQUIRED, "ignore path");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reportFormat = $input->getOption('report');
        $reportFormat = $reportFormat ? $reportFormat : "summary";
        $file = $input->getOption('file');
        $cbf = $input->getOption('cbf');
        $ignore = $input->getOption('ignore');

        $phpbin = $this->getPhpBinary();
        $configSet = [
            "default_standard" => "PSR2",
            "colors" => 1,
            "tab_width" => 4,
            "show_warnings"=>0
        ];

        if ($reportFormat) {
            $configSet['report_format'] = $reportFormat;
        }

        $rootPath = APPLICATION_PATH."../";

        foreach ($configSet as $k => $v) {
            $cmdPre = $phpbin . " " . $rootPath . "/vendor/bin/phpcs --config-set " . $k . " " . $v;
            exec($cmdPre);
        }

        if ($cbf) {
            $cmd = $phpbin . " " . $rootPath . "/vendor/bin/phpcbf " . $cbf . " --no-patch --extensions=php --ignore=*/view/*,*/config/*,*/tests/*,*/flatblib/*,*/runtime/*,*/vendor/*";
            exec($cmd, $str);
            $output->writeln($str);
        }

        if ($file) {
            $fileStr = "--report-full=" . $file;
        } else {
            $fileStr = "";
        }


        $cmd = $phpbin . " " . $rootPath . "/vendor/bin/phpcs -s $fileStr --extensions=php --ignore=*/view/*,*/config/*,*/tests/*,*/flatblib/*,*/runtime/*,*/vendor/*," . $ignore." ".APPLICATION_PATH . "";
        exec($cmd, $str);
        $output->writeln($str);
    }

    protected function getPhpBinary()
    {
        $executableFinder = new \Symfony\Component\Process\PhpExecutableFinder();
        return $executableFinder->find();
    }
}
