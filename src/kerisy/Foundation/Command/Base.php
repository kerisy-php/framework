<?php
/**
 * User: Peter Wang
 * Date: 16/10/19
 * Time: 下午3:06
 */

namespace Kerisy\Foundation\Command;

use Kerisy\Foundation\Bootstrap\Bootstrap;
use Kerisy\Console\Command\Command;

class Base extends Command
{
  public function __construct()
  {
      parent::__construct();
      Bootstrap::getInstance(ROOT_PATH);
  }
}