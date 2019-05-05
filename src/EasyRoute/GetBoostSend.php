<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/5
 * Time: 18:25
 */

namespace GoSwoole\Plugins\EasyRoute;


use GoSwoole\BaseServer\Server\Server;

trait GetBoostSend
{
    /**
     * 增强send，可以根据不同协议转码发送
     * @param $fd
     * @param $data
     * @return bool
     */
    public function autoBoostSend($fd, $data): bool
    {
        $easyRoutePlugin = Server::$instance->getPlugManager()->getPlug(EasyRoutePlugin::class);
        if ($easyRoutePlugin instanceof EasyRoutePlugin) {
            return $easyRoutePlugin->getRouteAspect()->autoBoostSend($fd, $data);
        }
        return false;
    }
}