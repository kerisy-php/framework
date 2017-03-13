<?php

namespace Kerisy\Mvc\View\Engine\Blade\Engines;

use Exception;
use ErrorException;
use Kerisy\Mvc\View\Engine\Blade\Compilers\CompilerInterface;
use Kerisy\Di\Di;
use Kerisy\Support\RunMode;

class CompilerEngine extends PhpEngine
{
    /**
     * The Blade compiler instance.
     *
     * @var \Kerisy\Mvc\View\Engine\Blade\Compilers\CompilerInterface
     */
    protected $compiler;

    /**
     * A stack of the last compiled templates.
     *
     * @var array
     */
    protected $lastCompiled = [];

    protected $runMode = null;
    
    /**
     * Create a new Blade view engine instance.
     *
     * @param  \Kerisy\Mvc\View\Engine\Blade\Compilers\CompilerInterface  $compiler
     * @return void
     */
    public function __construct(CompilerInterface $compiler, $runMode)
    {
        $this->compiler = $compiler;
        $this->runMode = $runMode;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array   $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        $env = (Object)$data['__env'];
        $result =  $this->_get($path, $data);
        $env->decrementRender();
        $doneRendering = $env->doneRendering();
        $env->incrementRender();
        return $result;
    }
    
    protected function _get($path, array $data = [])
    {
        $this->lastCompiled[] = $path;

        // If this given view has expired, which means it has simply been edited since
        // it was last compiled, we will re-compile the views so we can evaluate a
        // fresh copy of the view. We'll pass the compiler the path of the view.
        if($this->runMode == RunMode::RUN_MODE_ONLINE){
            if ($this->compiler->isExpired($path)) {
                $this->compiler->compile($path);
            }
        }else{
            $this->compiler->compile($path);
        }

        $compiled = $this->compiler->getCompiledPath($path);
        // Once we have the path to the compiled file, we will evaluate the paths with
        // typical PHP just like any other templates. We also keep a stack of views
        // which have been rendered for right exception messages to be generated.
        $results = $this->evaluatePath($compiled, $data);
        array_pop($this->lastCompiled);

        return $results;
    }

    /**
     * Handle a view exception.
     *
     * @param  \Exception  $e
     * @param  int  $obLevel
     * @return void
     *
     * @throws $e
     */
    protected function handleViewException($e, $obLevel)
    {
        $e = new ErrorException($this->getMessage($e), 0, 1, $e->getFile(), $e->getLine(), $e);

        $this->handlePhpViewException($e, $obLevel);
    }

    /**
     * Get the exception message for an exception.
     *
     * @param  \Exception  $e
     * @return string
     */
    protected function getMessage(Exception $e)
    {
        return $e->getMessage().' (View: '.realpath(last($this->lastCompiled)).')';
    }

    /**
     * Get the compiler implementation.
     *
     * @return \Kerisy\Mvc\View\Engine\Blade\Compilers\CompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }
}
