<?php

namespace Kerisy\Core;

use Kerisy\Core\Object;
use Kerisy\Core\Router;
use Kerisy\Http\Request;

/**
 * Class Dispatcher
 *
 * @package Kerisy\Core
 */
class Dispatcher extends Object
{
    protected $_router;

    public function init()
    {
        $this->_router = Router::getInstance();
    }

    public function getRouter()
    {
        return $this->_router;
    }

    public function dispatch($request)
    {
        // TODO
        $route = $this->_router->routing($request);

        return $route;
    }
}
