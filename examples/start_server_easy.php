<?php

use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\EasyRoute\EasyRoutePlugin;
use ESD\Server\Co\ExampleClass\DefaultServer;

require __DIR__ . '/../vendor/autoload.php';

define("ROOT_DIR", __DIR__ . "/..");
define("RES_DIR", __DIR__ . "/resources");
$server = new DefaultServer(null);
$server->getPlugManager()->addPlug(new AopPlugin());
$server->getPlugManager()->addPlug(new EasyRoutePlugin());
//配置
$server->configure();
//启动
$server->start();
