<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei<haoyf@putao.com>
 * Date: 2015/12/2
 * Time: 14:51
 */

namespace Kerisy\Support;

use Kerisy\Http\Client;


trait Account
{
    protected $client;

    /**
     * @describe 验证通过
     * @auth haoyanfei<haoyf@putao.com>
     * @param $credentials [id,token]
     * @return $result ['uid','token','nickname']
     */
    public function authorizable($credentials = [])
    {

        $this->client = new Client;
        $account = config('config')->get('account');
        $sign = strtoupper(md5($credentials['uid'] . $credentials['token'] . $account['secret_key']));
        if ($sign != $credentials['sign']) {
            //return false;
        }
        $toAccount = [
            'uid' => $credentials['uid'],
            'token' => $credentials['token'],
            'sign' => $credentials['sign'],
            'redirect' => $credentials['redirect']
        ];
        $result = $this->client->post($account['check_login_url'], $toAccount);

        if (!empty($result['error_code'])) {
            echo __FILE__;
            var_dump($result);
            return false;
        }
        $sign = strtoupper(md5($result['uid'] . $result['token'] . $account['secret_key']));
        if ($sign != $result['sign']) {
            return false;
        }
        return ['id' => $result['uid'], 'nickname' => $result['nickname'], 'token' => $result['token']];
    }
} 