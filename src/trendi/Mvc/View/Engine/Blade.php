<?php
/**
 *  laravel blade 模板
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午2:01
 */
namespace Trendi\Mvc\View\Engine;

use Trendi\Config\Config;
use Trendi\Mvc\View\Engine\Blade\Engines\EngineResolver;
use Trendi\Mvc\View\ViewInterface;
use Trendi\Mvc\View\Engine\Blade\Compilers\BladeCompiler;
use Trendi\Mvc\View\Engine\Blade\Engines\CompilerEngine;
use Trendi\Mvc\View\Engine\Blade\Engines\PhpEngine;
use Trendi\Mvc\View\Engine\Blade\FileViewFinder;
use Trendi\Mvc\View\Engine\Blade\Factory;
use Trendi\Support\Dir;

class Blade implements ViewInterface
{
    protected static $instance = null;

    /**
     * Engine Resolver
     *
     * @var
     */
    protected $engineResolver;

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
     * @param  \Trendi\Mvc\View\Engine\Blade\Engines\EngineResolver  $resolver
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
     * @param  \Trendi\Mvc\View\Engine\Blade\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerBladeEngine($resolver)
    {
        $resolver->register('blade', function () {
            $cachePath =  $this->cachePath;
            $compiler = new BladeCompiler($cachePath);
            $compiler->directive('datetime', function($timestamp) {
                return preg_replace('/(\(\d+\))/', '<?php echo date("Y-m-d H:i:s", $1); ?>', $timestamp);
            });
            $engine = new CompilerEngine($compiler);
            return $engine;
        });
    }


}