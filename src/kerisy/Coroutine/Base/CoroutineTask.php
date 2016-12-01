<?php
/**
 *  初始化
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Coroutine\Base;

use Kerisy\Server\Facade\Context;
use Kerisy\Support\Exception;
use Kerisy\Support\Log;
use Kerisy\Support\Exception\RuntimeExitException;
use Kerisy\Mvc\Route\Base\Exception\ResourceNotFoundException;
use Kerisy\Support\Exception\Page404Exception;
use Kerisy\Coroutine\Event;

class CoroutineTask{
    protected $callbackData;
    protected $stack;
    protected $callData;
    protected $routine;
    protected $exception = null;
    protected $i;

    public function __construct(\Generator $routine)
    {
        $this->routine = $routine;
        $this->stack = new \SplStack();
    }


    /**
     * 协程调度器
     * @param \Generator $routine
     */
    public function work(\Generator $routine){
        while (true) {
            try {
                if(!empty($this->exception)){
                    throw new \Exception($this->exception);
                }
                if (!$routine) {
                    return false;
                }
                $value = $routine->current();
                
//                dump("Coroutine run ...");
                //嵌套的协程
                if ($value instanceof \Generator) {
                    $this->stack->push($routine);
                    $routine = $value;
                    continue;
                }
                //异步IO的父类
                if(is_subclass_of($value, 'Kerisy\Coroutine\Base\CoroutineBase')){
                    $this->stack->push($routine);
                    $value->send([$this, 'callback']);
                    return;
                }

                if ($value instanceof \Swoole\Coroutine\RetVal) {
                    // end yeild
                    Log::syslog(__METHOD__ . " yield end words == " . print_r($value, true), __CLASS__);
                    return false;
                }

                if($value===null) {
                    try {
                        $return = $routine->getReturn();
                    }catch(\Exception $e){
                        $return = null;
                    }
                    if(!empty($return)){
                        $this->callbackData = $return;
                    }
                    if (!$this->stack->isEmpty()) {
                        $routine = $this->stack->pop();
                        $routine->send($this->callbackData);
                        continue;
                    } else {
                        if (!$this->routine->valid()) {
                            return false;
                        } else {
                            $this->routine->next();
                            continue;
                        }
                    }
                }else{
                    $this->routine->send($value);
                    return false;
                }
            } catch (Page404Exception $e){
                while(!$this->stack->isEmpty()) {
                    $routine = $this->stack->pop();
                }
                Event::fire("404",[$e,"Page404Exception",Context::response()]);
                break;
            }catch (ResourceNotFoundException $e){
                while(!$this->stack->isEmpty()) {
                    $routine = $this->stack->pop();
                }
                Event::fire("404",[$e,"ResourceNotFoundException",Context::response()]);
                break;
            }catch (RuntimeExitException $e){
                while(!$this->stack->isEmpty()) {
                    $routine = $this->stack->pop();
                }
                Log::sysinfo("RuntimeExitException:".$e->getMessage());
                break;
            }catch (\Exception $e) {
                while(!$this->stack->isEmpty()) {
                    $routine = $this->stack->pop();
                }
                Log::error(Exception::formatException($e));
                break;
            }catch (\Error $e) {
                while(!$this->stack->isEmpty()) {
                    $routine = $this->stack->pop();
                }
                Log::error(Exception::formatException($e));
                break;
            }
        }
    }
    /**
     * [callback description]
     * @param  [type]   $r        [description]
     * @param  [type]   $key      [description]
     * @param  [type]   $calltime [description]
     * @param  [type]   $res      [description]
     * @return function           [description]
     */
    public function callback($data)
    {
        /*
            继续work的函数实现 ，栈结构得到保存
         */
//        Log::log('callback:'.__METHOD__.print_r($data, true));
        if(!empty($data['exception'])){
            Log::error($data['exception']);
        }else {
            $gen = $this->stack->pop();
            $this->callbackData = $data;
            $value = $gen->send($this->callbackData);
            $this->work($gen);
        }


    }


    /**
     * [isFinished 判断该task是否完成]
     * @return boolean [description]
     */
    public function isFinished()
    {
        return $this->stack->isEmpty() && !$this->routine->valid();
    }

    public function getRoutine()
    {
        return $this->routine;
    }
}