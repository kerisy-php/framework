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
        server_name     rpc.test;
        root            /mnt/hgfs/code/kerisy_rpc;
        access_log      /var/log/nginx/kerisy_rpc.access.log;
        error_log       /var/log/nginx/kerisy_rpc.error.log;
        index  index.php index.html;

        if (!-e $request_filename) {
            rewrite ^(.*)$ /index.php$1 last;
        }

        location ~ ^(.+?\.php)(/.*)?$ {
                try_files $1 = 404;

                fastcgi_split_path_info ^(.+\.php)(/.+)$;
                include fastcgi_params;

                fastcgi_param SCRIPT_FILENAME $document_root$1;
                fastcgi_param PATH_INFO $2;
                fastcgi_param HTTPS on;
                fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
        }

        location ~ ^/(images|video|static)/ {
                root /mnt/hgfs/code/kerisy_rpc;
                #expires 30d;
        }
}

```



