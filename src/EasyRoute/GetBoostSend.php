<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 18:25
 */

namespace ESD\Plugins\EasyRoute;


use ESD\BaseServer\Server\Server;

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