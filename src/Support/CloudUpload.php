<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei<haoyf@putao.com>
 * Date: 2015/12/7
 * Time: 17:48
 */

namespace Kerisy\Support;


use Kerisy\Http\Client;
use Symfony\Component\Translation\Exception\InvalidResourceException;

class CloudUpload
{
    static public $app_key;

    private $client;

    public function dealUpload($file)
    {
        set_time_limit(30);
        /***获取sha1***/
        $sha1 = sha1_file($file['tmp_name']);
        /***校验sha1***/

        $file_cloud = config('config')->get('file_cloud');
        $check = $this->curl($file_cloud['check'], ['sha1' => $sha1]);

//        var_dump($check);exit;
        $upload = ['appid' => $file_cloud['appid'], 'uploadToken' => $this->createToken(), 'filename' => $file['name']];
        if (isset($check['error_code'])) {
            /******图片不存在的情况下,添加附件上传信息*******/
            if ($check['error_code'] == 60006) {
                $upload['file'] = new \CURLFile($file['tmp_name'], $file['type'], $file['name']);
            } else {
                throw new InvalidResourceException('图片12服务器出现问题:' . $check['error'] . __LINE__);
            }
        } else {
            $upload['sha1'] = $sha1;
        }
        $result = $this->curl($file_cloud['write'], $upload);
        if (isset($result['error_code'])) {
            throw new InvalidResourceException('图片服务器出现问题: ' . __LINE__ . ' lines! error:' . $check['error']);
        }
        return [
            'status' => 'success',
            'msg' => [
                'file_name' => $result['hash'] . '.' . $result['ext'],
                'realname' => $file['name'],
                'ext' => $result['ext'],
                'size' => $file['size']
            ]
        ];
    }
    public function curl($url, $params)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1); //post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params); //设置传送的参数
        curl_setopt($curl, CURLOPT_HEADER, false); //设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //要求结果为字符串
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15); //设置等待时间
        $res = curl_exec($curl); //运行curl
        $no = curl_errno($curl);
        if ($no > 0) {
            throw new InvalidResourceException('upload to cloud has lost itself: ' . curl_error($curl));
        }
        //TODO 服务器请求URL和返回结果
        curl_close($curl);
        $result = json_decode($res, true);
        if (json_last_error() > 0) {
            throw new InvalidResourceException('json format is worry: ' . json_last_error());
        }
        return $result;
    }
    public function createToken()
    {
        self::$app_key = config('config')->get('file_cloud')['key'];
        $request = [
            'deadline' => time() + 24 * 3600
        ];
        $putPolicy = json_encode($request);
        $encodedPutPolicy = base64_urlSafeEncode($putPolicy);
        $sign = hash_hmac('sha1', $encodedPutPolicy, self::$app_key['secret'], true);
        $encodedSign = base64_urlSafeEncode($sign);
        $uploadToken = self::$app_key['access_key'] . ':' . $encodedSign . ':' . $encodedPutPolicy;
        return $uploadToken;
    }
} 