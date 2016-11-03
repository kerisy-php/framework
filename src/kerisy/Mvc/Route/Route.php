<?php
/**
 * route 处理
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Mvc\Route;

use Kerisy\Mvc\Route\Exception\InvalidArgumentException;

class Route
{

    /**
     * route group
     *
     * @param $params
     * @param $callback
     */
    public static function group($params, $callback)
    {
        $obj = new RouteGroup();

        if (is_array($params)) {

            if (isset($params['name'])) {
                $obj->setName($params['name']);
            }

            if (isset($params['prefix'])) {
                $obj->setPrefix($params['prefix']);
            }

            if (isset($params['domain'])) {
                $obj->setDomain($params['domain']);
            }

            if (isset($params['middleware'])) {
                $obj->setMiddleware($params['middleware']);
            }

            if (isset($params['methods'])) {
                $obj->setMethods($params['methods']);
            }

        } elseif (is_string($params)) {
            $obj->setPrefix($params);
        }

        $obj->bind($callback);
    }


    /**
     * 方法 post  get put delete 等调用
     *
     * @param $method
     * @param $args
     * @return RouteBase
     * @throws InvalidArgumentException
     */
    public static function __callStatic($method, $args)
    {
        return self::bind($method, $args);
    }


    public static function bind($method, $args)
    {
        if (is_array($method)) {
            $_method = [];
            foreach ($method as $v) {
                $_method = strtoupper($v);
            }
        } else {
            $_method = strtoupper($method);
        }

        $obj = new RouteBase();
        if (count($args) < 2) {
            throw new InvalidArgumentException("argument count error");
        }

        $path = $args[0];
        $closureOrArr = $args[1];

        $obj->match($_method, $path, $closureOrArr);
//        dump($obj->getResult());
        if (is_array($closureOrArr)) {
            if (isset($closureOrArr['name'])) {
                $obj->name($closureOrArr['name']);
            }

            if (isset($closureOrArr['uses'])) {
                $obj->defaults(["_controller" => $closureOrArr['uses']]);
            }

            if (isset($closureOrArr['domain'])) {
                $obj->domain($closureOrArr['domain']);
            }

            if (isset($closureOrArr['middleware'])) {
                $obj->middleware($closureOrArr['middleware']);
            }

            if (isset($closureOrArr['where'])) {
                $obj->where($closureOrArr['where']);
            }

        }
        return $obj;
    }
}