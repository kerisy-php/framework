<?php
/**
 *  laravel blade 模板
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */
namespace Kerisy\Mvc\View\Engine;

use Kerisy\Config\Config;
use Kerisy\Mvc\View\Engine\Blade\Engines\EngineResolver;
use Kerisy\Mvc\View\ViewInterface;
use Kerisy\Mvc\View\Engine\Blade\Compilers\BladeCompiler;
use Kerisy\Mvc\View\Engine\Blade\Engines\CompilerEngine;
use Kerisy\Mvc\View\Engine\Blade\Engines\PhpEngine;
use Kerisy\Mvc\View\Engine\Blade\FileViewFinder;
use Kerisy\Mvc\View\Engine\Blade\Factory;
use Kerisy\Support\Dir;

class Blade implements ViewInterface
{
    protected static $instance = null;

    /**
     * Engine Resolver
     *
     * @var
     */
    protected $engineResolver;

    protected $config = null;


    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        require_once __DIR__."/Blade/helper.php";
    }

    public static function getInstance()
    {
        if (self::$instance) return self::$instance;
        return self::$instance = new self();
    }

    public function setViewRootPath($path)
    {
        $this->viewPaths = $path;
    }

    public function setCachePath($path)
    {
        $this->cachePath = $path;
    }

    public function getView()
    {
        return $this->engineResolver->resolve('blade');
    }


    public function setConfig($config)
    {
        $this->config = $config;
    }


    /**
     * Render shortcut.
     *
     * @param  string $view
     * @param  array $data
     *
     * @return string
     */
    public function render($view, $data = [])
    {
        $path = $this->viewPaths;
        $resolver = new EngineResolver();
        foreach (['php', 'blade'] as $engine) {
            $this->{'register'.ucfirst($engine).'Engine'}($resolver);
        }
        $finder = new FileViewFinder([$path]);

        $fisConfig = "";
        $fisPath = Config::get("_release.path");
        
        if($fisPath){
            $fisConfig = Dir::formatPath($fisPath).Config::get("app.view.fis.map_path");
        }
        $factory = new Factory($resolver, $finder, $fisConfig);
        
        $result= $factory->make($view, $data, [])->render();

        return $result;
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param  \Kerisy\Mvc\View\Engine\Blade\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerPhpEngine($resolver)
    {
        $resolver->register('php', function () {
            return new PhpEngine;
        });
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param  \Kerisy\Mvc\View\Engine\Blade\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerBladeEngine($resolver)
    {
        $resolver->register('blade', function () {
            $cachePath =  $this->cachePath;
            $compiler = new BladeCompiler($cachePath);
            $bladeEx= $this->config;

            if($bladeEx){
                foreach ($bladeEx as $k=>$class){
                    $compiler->directive($k, function($param) use ($class){
                        $obj = new $class();
                        return $obj->perform($param);
                    });
                }
            }
            $engine = new CompilerEngine($compiler);
            return $engine;
        });
    }


}