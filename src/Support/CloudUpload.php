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

        $this->client = new Client;
        /***获取sha1***/
        $sha1 = sha1_file($file['tmp_name']);
        /***校验sha1***/

        $upload = config('config')->get('file_cloud');

        $check = $this->client->post($upload['check'], ['sha1' => $sha1],['']);

        $upload = ['appid' => $upload['appid'], 'uploadToken' => $this->createToken(), 'filename' => $file['name']];

        if (isset($check['error_code'])) {
            /******图片不存在的情况下,添加附件上传信息*******/
            if ($check['error_code'] == 60006) {
                $upload['file'] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
            } else {
                throw new InvalidResourceException('图片12服务器出现问题:' . $check['error'] . __LINE__);
            }
        } else {
            $upload['sha1'] = $sha1;
        }
        $result = $this->client->post($upload['write'], $upload);
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