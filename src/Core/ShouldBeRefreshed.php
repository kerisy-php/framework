<?php
/**
 * Kerisy Framework
 * 
 * PHP Version 7
 * 
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Core
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Core;

/**
 * This interface is used to indicate that a service should be refreshed after every request, such as `Kerisy\HttpRequest`
 * and `Kerisy\HttpResponse`.
 *
 * @package Kerisy\Core
 */
interface ShouldBeRefreshed
{

}
