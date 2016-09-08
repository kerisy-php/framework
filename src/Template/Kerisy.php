<?php
/**
 * Kerisy框架模板处理
 *
 * User: wangkaihui
 * Date: 16/8/10
 * Time: 下午8:18
 */

namespace Kerisy\Template;


class Kerisy extends Base
{

    private $path = null;
    private $view = null;

    /**
     * 获取模板解析内容
     *
     * @param $tplPath
     * @param $data
     * @return mixed
     */
    public function render($data)
    {
        $this->view->replace($data);
        $html = $this->view->render($this->path);

        return $html;
    }

    /**
     * 获取模板路径
     *
     * @param string $tplPath
     * @param null $viewObj
     * @return mixed
     */
    public function path($tplPath, $viewObj = null)
    {

        $this->view = $viewObj;

        $this->path = $tplPath;

        return $tplPath;
    }
}