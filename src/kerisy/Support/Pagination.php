<?php
/**
 *
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */


namespace Kerisy\Support;

use Exception;
use Kerisy\Mvc\Route\RouteMatch;

/**
 * Class Pagination
 *
$pagination = new Pagination($this->request->query->all(),$page,$total);
$pagination->setRPP($pageSize);
$pagination->setRoute(['core/accessgroup/addUser', ['id'=>$id]]);
 *
 * @package Kerisy\Support
 */
class Pagination
{

    protected $_variables = array(
        'classes' => array('clearfix', 'pagination'),
        'crumbs' => 5,  //分页最多显示5个
        'rpp' => 20,//每页步长
        'key' => 'p',
        'next' => '下一页 &raquo;',
        'previous' => '&laquo; 上一页',
        'alwaysShowPagination' => false,//如果只有一页是否显示
        'clean' => false,
        "route" => "",//路由设置
    );

    public function setRoute($route)
    {
        $this->_variables['route'] = $route;
    }

    /**
     * __construct
     *
     * @access public
     * @param  integer $current (default: null)
     * @param  integer $total (default: null)
     * @return void
     */
    public function __construct($get=null, $current = null, $total = null)
    {
        // current instantiation setting
        if (!is_null($current)) {
            $this->setCurrent($current);
        }
        // total instantiation setting
        if (!is_null($total)) {
            $this->setTotal($total);
        }
        // Pass along get (for link generation)
        $this->_variables['get'] = $get;
    }

    /**
     * _check
     *
     * Checks the current (page) and total (records) parameters to ensure
     * they've been set. Throws an exception otherwise.
     *
     * @access protected
     * @return void
     */
    protected function _check()
    {
        if (!isset($this->_variables['current'])) {
            throw new Exception('Pagination::current must be set.');
        } elseif (!isset($this->_variables['total'])) {
            throw new Exception('Pagination::total must be set.');
        }
    }

    /**
     * addClasses
     *
     * Sets the classes to be added to the pagination div node.
     * Useful with Twitter Bootstrap (eg. pagination-centered, etc.)
     *
     * @see    <http://twitter.github.com/bootstrap/components.html#pagination>
     * @access public
     * @param  mixed $classes
     * @return void
     */
    public function addClasses($classes)
    {
        $this->_variables['classes'] = array_merge(
            $this->_variables['classes'],
            (array)$classes
        );
    }

    /**
     * alwaysShowPagination
     *
     * Tells the rendering engine to show the pagination links even if there
     * aren't any pages to paginate through.
     *
     * @access public
     * @return void
     */
    public function alwaysShowPagination()
    {
        $this->_variables['alwaysShowPagination'] = true;
    }


    protected function getRouteUrl($page = 1)
    {
        $page = $page > 1 ? $page : 1;
        $routeData = $this->_variables['route'];
        $key = $this->_variables['key'];
        list($route, $params) = $routeData;
        $params[$key] = $page;
        return RouteMatch::getInstance()->simpleUrl($route, $params);
    }

    /**
     * parse
     *
     * Parses the pagination markup based on the parameters set and the
     * logic found in the render.inc.php file.
     *
     * @access public
     * @return void
     */
    public function parse()
    {
        // ensure required parameters were set
        $this->_check();
        $total = $rpp = $current = $alwaysShowPagination = $classes = $get = $previous = $clean = $crumbs = $next = null;
        foreach ($this->_variables as $_name => $_value) {
            $$_name = $_value;
        }
        $_response = $this->_getRender(
            $total, $rpp, $current, $alwaysShowPagination, $classes, $get,
            $previous, $clean, $crumbs, $next
        );
        return $_response;
    }

    /**
     * 获取匹配
     */
    private function _getRender(
        $total, $rpp, $current, $alwaysShowPagination, $classes, $get,
        $previous, $clean, $crumbs, $next
    )
    {
        $str = "";
        $pages = ((int)ceil($total / $rpp));
        // if it's an invalid page request
        if ($current < 1) {
            return;
        } elseif ($current > $pages) {
            return;
        }
        // if there are pages to be shown
        if ($pages > 1 || $alwaysShowPagination === true) {
            $classSTr = implode(' ', $classes);
            $str .= "<ul class=\"{$classSTr}\">";
            $classes = array('copy', 'previous');
            $params = $get;
            $p = ($current - 1);
            $href = $this->getRouteUrl($p);
            if($params){
                $href .= '?' . http_build_query($params);
            }
            $href = preg_replace(
                array('/=$/', '/=&/'),
                array('', '&'),
                $href
            );
            if ($current === 1) {
                $href = 'javascript:;';
                array_push($classes, 'disabled');
            }
            $classSTr = implode(' ', $classes);
            $str .= "<li class=\"{$classSTr}\"><a href=\"{$href}\">{$previous}</a></li>";
            if (!$clean) {
                /**
                 * Calculates the number of leading page crumbs based on the minimum
                 *     and maximum possible leading pages.
                 */
                $max = min($pages, $crumbs);
                $limit = ((int)floor($max / 2));
                $leading = $limit;
                for ($x = 0; $x < $limit; ++$x) {
                    if ($current === ($x + 1)) {
                        $leading = $x;
                        break;
                    }
                }
                for ($x = $pages - $limit; $x < $pages; ++$x) {
                    if ($current === ($x + 1)) {
                        $leading = $max - ($pages - $x);
                        break;
                    }
                }
                // calculate trailing crumb count based on inverse of leading
                $trailing = $max - $leading - 1;
                // generate/render leading crumbs
                for ($x = 0; $x < $leading; ++$x) {
                    // class/href setup
                    $params = $get;
                    $p = ($current + $x - $leading);
                    $href = $this->getRouteUrl($p);
                    if($params){
                        $href .= '?' . http_build_query($params);
                    }
                    $href = preg_replace(
                        array('/=$/', '/=&/'),
                        array('', '&'),
                        $href
                    );
                    $tmp1 = $current + $x - $leading;
                    $tmp2 = $current + $x - $leading;
                    $str .= "<li class=\"number\"><a data-pagenumber=\"{$tmp1}\" href=\"{$href}\">{$tmp2}</a></li>";
                }
                $str .= "<li class=\"number active\"><a data-pagenumber=\"{$current}\" href=\"javascript:;\">{$current}</a></li>";
                // generate/render trailing crumbs
                for ($x = 0; $x < $trailing; ++$x) {
                    // class/href setup
                    $params = $get;
                    $p = ($current + $x + 1);
                    $href = $this->getRouteUrl($p);
                    if($params){
                        $href .= '?' . http_build_query($params);
                    }
                    $href = preg_replace(
                        array('/=$/', '/=&/'),
                        array('', '&'),
                        $href
                    );
                    $tmp4 = $current + $x + 1;
                    $tmp5 = $current + $x + 1;
                    $str .= "<li class=\"number\"><a data-pagenumber=\"{$tmp4}\" href=\"{$href}\">{$tmp5}</a></li>";
                }
            }
            /**
             * Next Link
             */
            // anchor classes and target
            $classes = array('copy', 'next');
            $params = $get;
            $p = ($current + 1);
            $href = $this->getRouteUrl($p);
            if($params){
                $href .= '?' . http_build_query($params);
            }
            $href = preg_replace(
                array('/=$/', '/=&/'),
                array('', '&'),
                $href
            );
            if ($current === $pages) {
                $href = 'javascript:;';
                array_push($classes, 'disabled');
            }
            $tmp6 = implode(' ', $classes);
            $str .= "<li class=\"{$tmp6}\"><a href=\"{$href}\">{$next}</a></li></ul>";
        }
        return $str;
    }

    /**
     * setClasses
     *
     * @see    <http://twitter.github.com/bootstrap/components.html#pagination>
     * @access public
     * @param  mixed $classes
     * @return void
     */
    public function setClasses($classes)
    {
        $this->_variables['classes'] = (array)$classes;
    }

    /**
     * setClean
     *
     * Sets the pagination to exclude page numbers, and only output
     * previous/next markup. The counter-method of this is self::setFull.
     *
     * @access public
     * @return void
     */
    public function setClean()
    {
        $this->_variables['clean'] = true;
    }

    /**
     * setCrumbs
     *
     * Sets the maximum number of 'crumbs' (eg. numerical page items)
     * available.
     *
     * @access public
     * @param  integer $crumbs
     * @return void
     */
    public function setCrumbs($crumbs)
    {
        $this->_variables['crumbs'] = $crumbs;
    }

    /**
     * setCurrent
     *
     * Sets the current page being viewed.
     *
     * @access public
     * @param  integer $current
     * @return void
     */
    public function setCurrent($current)
    {
        $this->_variables['current'] = (int)$current;
    }

    /**
     * setFull
     *
     * See self::setClean for documentation.
     *
     * @access public
     * @return void
     */
    public function setFull()
    {
        $this->_variables['clean'] = false;
    }

    /**
     * setKey
     *
     * Sets the key of the <_GET> array that contains, and ought to contain,
     * paging information (eg. which page is being viewed).
     *
     * @access public
     * @param  string $key
     * @return void
     */
    public function setKey($key)
    {
        $this->_variables['key'] = $key;
    }

    /**
     * setNext
     *
     * Sets the copy of the next anchor.
     *
     * @access public
     * @param  string $str
     * @return void
     */
    public function setNext($str)
    {
        $this->_variables['next'] = $str;
    }

    /**
     * setPrevious
     *
     * Sets the copy of the previous anchor.
     *
     * @access public
     * @param  string $str
     * @return void
     */
    public function setPrevious($str)
    {
        $this->_variables['previous'] = $str;
    }

    /**
     * setRPP
     *
     * Sets the number of records per page (used for determining total
     * number of pages).
     *
     * @access public
     * @param  integer $rpp
     * @return void
     */
    public function setRPP($rpp)
    {
        $this->_variables['rpp'] = (int)$rpp;
    }

    /**
     * setTotal
     *
     * Sets the total number of records available for pagination.
     *
     * @access public
     * @param  integer $total
     * @return void
     */
    public function setTotal($total)
    {
        $this->_variables['total'] = (int)$total;
    }
}