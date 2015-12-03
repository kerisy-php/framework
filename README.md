Kerisy - A web framework written in PHP 7.0+
===========================================================

Kerisy use swoole http server.


1.增加auth(目前只增加了api形式的调用的验证).
    a. Kersiy\Auth\Middleware\ClientAccount.php
        客户端使用说明:
                参数访问登录页面 必须携带 uid 和 token
        服务端调用:
            目标控制器 use \Kerisy\Support\Auth
            路由就可以调用该控制器的 login 或者 logout
                . 成功 httpCode = 200 data = [userInfo];
                . 失败 httpCode = 4011 msg = "登录失败"
    b. clone a.... 修改 handle

    httpCode: 4010:未登录 4011:登录失败 200:成功