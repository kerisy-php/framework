<?php
/**
 * @project            Kerisy Framework
 * @author             Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright         (c) 2015 putao.com, Inc.
 * @package            kerisy/framework
 * @create             2015/11/11
 * @version            2.0.0
 */

namespace Kerisy\Session;

use Kerisy\Core\Object;
use Kerisy\Support\BagTrait;

/**
 * The container of session key-value pairs.
 *
 * @package Kerisy\Session
 */
class Session extends Object
{
    use BagTrait;

    /**
     * The id of the session, this is possible null when the session is not actually stored.
     *
     * @var string|null
     */
    public $id;

    public function __construct(array $attributes = [], $config = [])
    {
        $this->replace($attributes);

        parent::__construct($config);
    }
}
