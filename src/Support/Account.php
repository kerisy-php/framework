<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei<haoyf@putao.com>
 * Date: 2015/12/2
 * Time: 14:51
 */

namespace Kerisy\Support;

use Kerisy\Http\Client;

/**
 * @describe only support api
 * Class Account
 * @package Kerisy\Support
 */
trait Account
{
    protected $client;

    /**
     * @describe 验证通过
     * client----uid,token,appid,sign
     * @auth haoyanfei<haoyf@putao.com>
     * @param $credentials [id,token]
     * @return $result ['uid','token','nickname']
     */
    public function authorizable($credentials = [])
    {
        $this->client = new Client;
        $account = config('config')->get('account');

        $sign = makeVerify($credentials, $account['secret_key']);

        if ($sign != $credentials['sign']) {
            return false;
        }
        $result = $this->client->post($account['checkToken'], $credentials);
        if (isset($result['error_code']) && $result['error_code'] === 0) {
            return true;
        }
        return false;
    }

    public function getNickName($credentials = [])
    {
        $this->client = new Client;
        $account = config('config')->get('account');

        $sign = makeVerify($credentials, $account['secret_key']);

        if ($sign != $credentials['sign']) {
            return false;
        }
        $result = $this->client->post($account['getNickName'], $credentials);
        if (isset($result['error_code']) && $result['error_code'] === 0) {
            return $result['msg'];
        }
        return false;
    }
} 