<?php

namespace Trendi\Mvc\View\Engine\Blade\Engines;

use Exception;
use Throwable;
use Trendi\Support\Log;

class PhpEngine implements EngineInterface
{
    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array   $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        return $this->evaluatePath($path, $data);
    }

    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param  string  $__path
     * @param  array   $__data
     * @return string
     */
    protected function evaluatePath($__path, $__data)
    {
        $obLevel = ob_get_level();
        ob_start();
        extract($__data, EXTR_SKIP);
        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            include $__path;
        } catch (Exception $e) {
            $this->handlePhpViewException($e, $obLevel);
        } catch (\Error $e) {
            $this->handlePhpViewException($e, $obLevel);
        }catch (Throwable $e) {
            $this->handlePhpViewException(new Exception($e), $obLevel);
        }
        return ltrim(ob_get_clean());
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
    protected function handlePhpViewException($e, $obLevel=0)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }
//        throw $e;
        Log::error(\Trendi\Support\Exception::formatException($e));
    }
}
