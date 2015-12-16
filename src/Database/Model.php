<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Database
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Database;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    protected $connection = 'default';

    public function __construct(array $attributes = [])
    {
        DB::signton($this->connection);
        parent::__construct($attributes);
    }
}
