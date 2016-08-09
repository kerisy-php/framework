#Kerisy - A web framework written in PHP 7.0+
===========================================================

#####Kerisy use swoole http server.

web server run command : php kerisy server start

rpc server run command : php kerisy rpcserver start

job server run command : php kerisy jobserver start (first import jobs.sql)

#####use nginx  http server

* add "index.php", write follow code:

```
<?php

require __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', 1);

defined('APPLICATION_PATH') || define('APPLICATION_PATH', __DIR__ . '/application/');
defined('CONFIG_PATH') || define('CONFIG_PATH', APPLICATION_PATH . '/config/');

defined('KERISY_ENV') || define('KERISY_ENV', getenv('KERISY_ENV')?:'development');

defined('CLI_MODE') || define('CLI_MODE', PHP_SAPI === 'cli' );

$app = new \Kerisy\Core\Application\Web();
$app->webHandle();

```

* nginx conf

```

server {
        listen          80;
        server_name     admin.statistics.ptdev.cn api.statistics.ptdev.cn;
        root            /home/sysadm/statistics;
        access_log      /var/log/nginx/statistics.access.log;
        error_log       /var/log/nginx/statistics.error.log;

        if (!-e $request_filename) {
            rewrite ^(.*)$ /index.php$1 last;
        }

        location ~ .*\.php(\/.*)*$ {
            include fastcgi.conf;
            fastcgi_pass  127.0.0.1:9000;
        }

        location ~ ^/(images|video|static)/ {
                root /home/sysadm/statistics/public;
                #expires 30d;
        }
}

```



