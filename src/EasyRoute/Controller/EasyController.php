<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 16:20
 */

namespace GoSwoole\Plugins\EasyRoute\Controller;


use GoSwoole\BaseServer\Server\Server;
use Monolog\Logger;

abstract class EasyController implements IController
{
    public function log($level, $message)
    {
        Server::$instance->getLog()->log($level, $message);
    }
}