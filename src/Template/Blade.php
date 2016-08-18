<?php
/**
 * Blade 模板引擎
 *
 * User: wangkaihui
 * Date: 16/8/10
 * Time: 下午8:18
 */

namespace Kerisy\Template;

class Blade extends Base
{

    private $path = null;

    /**
     * 获取模板解析内容
     *
     * @param $tplPath
     * @param $data
     * @return mixed
     */
    public function render($data)
    {
        $viewPath = APPLICATION_PATH . 'views/';
        $compilePath = APPLICATION_PATH . "runtime/complie/";
        if (!is_dir($compilePath)) {
            mkdir($compilePath, 0777, true);
        }
        $obj = new \Jenssegers\Blade\Blade($viewPath, $compilePath);
        $html = $obj->render($this->path, $data);

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
        $template = $viewObj->getTplPath($tplPath);
        $template = str_replace("/", ".", $template);
//        $template = str_replace("\\", ".", $template);
        $this->path = $template;
        return $template;
    }
}