<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei<haoyf@putao.com>
 * Date: 2015/12/3
 * Time: 13:23
 */

namespace Kerisy\Support;

trait Auth
{
    public function login()
    {
        $params = request()->all();
        $user = auth()->attempt($params);
        if (auth()->check()) {
            return jsonSuccess($user);
        } else {
            return jsonError('登录失败', '4011');
        }
    }

    public function logout()
    {
        auth()->logout(request()->get('token'));
        return jsonSuccess('退出成功', '200');
    }
} 