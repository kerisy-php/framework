<?php
/**
 * rpc controller
 *
 * User: Peter Wang
 * Date: 16/9/19
 * Time: 上午11:09
 */

namespace Kerisy\Rpc;

use Kerisy\Support\ElapsedTime;

class Controller
{

    const RESPONSE_CODE = 200;
    const RESPONSE_NORMAL_ERROR_CODE = 500;

    /**
     * 数据返回
     * 
     * @param $data
     * @param int $errorCode
     * @param string $errodMsg
     * @return array
     */
    public function response($data, $errorCode = self::RESPONSE_CODE, $errodMsg = '')
    {
        $elapsedTime = ElapsedTime::runtime("rpc_sys_elapsed_time");
        $result = [];
        $result['result'] = $data;
        $result['errorCode'] = $errorCode;
        $result['errodMsg'] = $errodMsg;
        $result['elapsedTime'] = $elapsedTime;
        return $result;
    }
}