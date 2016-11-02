<?php
/**
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 上午9:09
 */

namespace Trendi\Foundation;

use Trendi\Di\Di;
use Trendi\Support\Arr;
use Trendi\Support\Dir;
use Trendi\Config\Config;
use Trendi\Http\Response;
use Trendi\Http\Request;
use Trendi\Mvc\AssignData;
use Trendi\Mvc\Template;
use Trendi\Support\Log;

class Controller
{

    /**
     * @var \Trendi\Http\View;
     */
    public $view;

    /**
     * @var \Trendi\Http\Request
     */
    protected $request = null;

    /**
     * @var \Trendi\Http\Response
     */
    protected $response = null;


    public function __construct(Request $request=null, Response $response=null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = new AssignData();
    }

    /**
     * 模板render
     *
     * @param $viewPath
     * @param array $assign
     * @return mixed
     * @throws \Trendi\Mvc\Exception\InvalidArgumentException
     */
    public function render($viewPath, $assign = [])
    {

        $fisPath = Config::get("_release.path");
        if($fisPath){
            $fis = Config::get("app.view.fis.view_path");
            $viewRoot = Dir::formatPath($fisPath).$fis;
        }else{
            $viewRoot = Config::get("app.view.path");
        }

        $theme = Config::get("app.view.theme");
        $realViewRoot = Dir::formatPath($viewRoot).$theme;
        Template::setViewRoot($realViewRoot);

        $viewCachePath = Config::get("app.view.compile_path");

        Template::setViewCacheRoot($viewCachePath);
        Template::setEngine(Config::get("app.view.engine"));
        $assign = Arr::merge($assign, $this->view->getAssignData());

        $content = Template::render($viewPath, $assign);
        
        return $content;
    }
    
}