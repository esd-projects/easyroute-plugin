<?php

use ESD\BaseServer\ExampleClass\Server\DefaultServer;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\EasyRoute\EasyRoutePlugin;

require __DIR__ . '/../vendor/autoload.php';

define("ROOT_DIR", __DIR__ . "/..");
define("RES_DIR", __DIR__ . "/resources");
$server = new DefaultServer();
$server->getPlugManager()->addPlug(new AopPlugin(new AopConfig()));
$server->getPlugManager()->addPlug(new EasyRoutePlugin());
//配置
$server->configure();
//启动
$server->start();
