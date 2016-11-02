<?php
/**
 * User: Peter Wang
 * Date: 16/10/19
 * Time: 下午3:06
 */

namespace Trendi\Foundation\Command;

use Trendi\Foundation\Bootstrap\Bootstrap;
use Trendi\Console\Command\Command;

class Base extends Command
{
  public function __construct()
  {
      parent::__construct();
      Bootstrap::getInstance(ROOT_PATH);
  }
}