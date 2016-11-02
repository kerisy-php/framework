<?php
/**
 * httpd 服务器
 *
 * User: Peter Wang
 * Date: 16/9/14
 * Time: 上午10:04
 */

namespace Trendi\Server;

use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use swoole_http_server as SwooleServer;
use Trendi\Http\Request;
use Trendi\Http\Response;
use Trendi\Server\Facade\Context;
use Trendi\Server\Facade\Task as FacadeTask;
use Trendi\Coroutine\Event;
use Trendi\Support\Facade;
use Trendi\Support\Log;
use Trendi\Support\Exception;
use Trendi\Support\ElapsedTime;
use Trendi\Support\Exception\RuntimeExitException;
use Trendi\Mvc\Route\Base\Exception\ResourceNotFoundException;
use Trendi\Support\Exception\Page404Exception;

class HttpServer
{
    /**
     * @var swooleServer
     */
    public $swooleServer = null;
    private $adapter = null;
    private $serverName = '';
    private $config = [];

    public function __construct(SwooleServer $swooleServer, array $config, $adapter, $serverName = "trendi")
    {
        $this->swooleServer = $swooleServer;
        $this->swooleServer->set($config);
        $this->config = $config;
        $this->adapter = $adapter;
        $this->serverName = $serverName."-httpd";
        $this->config['server_name'] =$this->serverName;
    }

    /**
     * 服务器开始
     */
    public function start()
    {
        $this->swooleServer->on('start', [$this, 'onStart']);
        $this->swooleServer->on('shutdown', [$this, 'onShutdown']);

        $this->swooleServer->on('managerStart', [$this, 'onManagerStart']);
        $this->swooleServer->on('managerStop', [$this, 'onManagerSop']);
        $this->swooleServer->on('workerStart', [$this, 'onWorkerStart']);
        $this->swooleServer->on('workerStop', [$this, 'onWorkerStop']);
        $this->swooleServer->on('workerError', [$this, 'onWorkerError']);

        $this->swooleServer->on('request', [$this, 'onRequest']);

        //开启任务
        if (isset($this->config['task_worker_num']) && ($this->config['task_worker_num'] > 0)) {
            $this->swooleServer->on('Task', array($this, 'onTask'));
            $this->swooleServer->on('Finish', array($this, 'onFinish'));
        }
        $this->swooleServer->start();
    }

    public function onManagerSop(SwooleServer $serv)
    {
        Log::sysinfo($this->serverName . " manage stop ......");
    }


    public function onManagerStart(SwooleServer $serv)
    {
        swoole_set_process_name($this->serverName . "-manage");
        Log::sysinfo($this->serverName . " manage start ......");
    }

    /**
     * 进程task
     *
     * @param SwooleServer $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     * @return array
     */
    public function onTask(SwooleServer $serv, $task_id, $from_id, $data)
    {
        try {
            return FacadeTask::start($data);
        } catch (\Exception $e) {
            $exception = \Trendi\Support\Exception::formatException($e);
            Log::error($exception);
            return [false, $data, $exception];
        } catch (\Error $e) {
            $exception = \Trendi\Support\Exception::formatException($e);
            Log::error($exception);
            return [false, $data, $exception];
        }
    }

    public function onFinish(SwooleServer $serv, $task_id, $data)
    {
        FacadeTask::finish($data);
    }


    public function onStart(SwooleServer $swooleServer)
    {
        swoole_set_process_name($this->serverName . "-master");
        Log::sysinfo($this->serverName . " server start ......");
        $memRebootRate = isset($this->config['mem_reboot_rate'])?$this->config['mem_reboot_rate']:0;
        
        Reload::load($this->serverName , $memRebootRate, $this->config);
    }

    public function onShutdown(SwooleServer $swooleServer)
    {
        Log::sysinfo($this->serverName . " server shutdown ...... ");
    }

    /**
     * 数据初始化
     *
     * @param SwooleServer $swooleServer
     * @param $workerId
     */
    public function onWorkerStart(SwooleServer $swooleServer, $workerId)
    {
        if (function_exists("apc_clear_cache")) {
            apc_clear_cache();
        }

        if (function_exists("apcu_clear_cache")) {
            apcu_clear_cache();
        }
        
        if (function_exists("opcache_reset")) {
            opcache_reset();
        }

        if ($workerId >= $this->config["worker_num"]) {
            swoole_set_process_name($this->serverName . "-task-worker");
            Log::sysinfo($this->serverName . " task worker start ..... ");
        } else {
            swoole_set_process_name($this->serverName . "-worker");
            Log::sysinfo($this->serverName . " worker start ..... ");
        }
        $this->adapter->httpBoostrap();

        if (Facade::getFacadeApplication()) {
            Context::set("server", $swooleServer, true, true);
            FacadeTask::setLogPath($this->config["task_fail_log"]);
            FacadeTask::setRetryCount($this->config["task_retry_count"]);
        }
    }

    public function onWorkerStop(SwooleServer $swooleServer, $workerId)
    {
        Log::sysinfo($this->serverName . " worker stop ..... ");
    }

    public function onWorkerError(SwooleServer $swooleServer, $workerId, $workerPid, $exitCode)
    {
        Log::sysinfo($this->serverName . " worker error ..... ");
        Log::sysinfo("======================");
        Log::error(socket_strerror($exitCode) . "");

        Event::fire("httpd.worker.error", [$exitCode, date('Y-m-d H:i:s')]);
    }

    /**
     * 请求处理
     *
     * @param SwooleHttpRequest $swooleHttpRequest
     * @param SwooleHttpResponse $swooleHttpResponse
     * @throws Exception\InvalidArgumentException
     * @throws \Trendi\Http\Exception\ContextErrorException
     */
    public function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
        ElapsedTime::setStartTime("sys_elapsed_time");

      
        $request = new Request($swooleHttpRequest);
       
        $response = new Response($swooleHttpResponse);

    
        if (Facade::getFacadeApplication()) {
            Context::clear();
            Context::set("response", $response);
            Context::set("request", $request);
            $request = Context::request();
            $response = Context::response();
        }
      
        $httpSendFile = new HttpSendFile($request, $response);
        $httpSendFile->setConfig($this->config);
        list($isFile,,,,) = $httpSendFile->analyse();
        
        if ($isFile) {
            $httpSendFile->sendFile();
        } else {
     
            $this->response($request, $response);

            if (Facade::getFacadeApplication()) {
                Context::clear();
            }
            Event::fire("clear");
        }
    }

    private function response(Request $request, Response $response)
    {
        try {
            $content = $this->requestHtmlHandle($request, $response);
            $response->end($content);
        }catch (Page404Exception $e){
            Event::fire("404",[$e,"Page404Exception",$response]);
        }catch (ResourceNotFoundException $e){
            Event::fire("404",[$e,"ResourceNotFoundException",$response]);
        }catch (RuntimeExitException $e){
            Log::syslog("RuntimeExitException:".$e->getMessage());
        }catch (\Exception $e) {
            $response->status(500);
            $response->end();
            Log::error(Exception::formatException($e));
        } catch (\Error $e) {
            $response->status(500);
            $response->end();
            Log::error(Exception::formatException($e));
        }
    }

    /**
     *  内容处理
     *
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    protected function requestHtmlHandle(Request $request, Response $response)
    {
        $gzip = isset($this->config["gzip"]) ? $this->config["gzip"] : 0;
        if ($gzip) {
            $response->gzip($gzip);
        }
       
        $response->header("Content-Type", "text/html;charset=utf-8");
        return $this->adapter->start($request, $response);
    }

}