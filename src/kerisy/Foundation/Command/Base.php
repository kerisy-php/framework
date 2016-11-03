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