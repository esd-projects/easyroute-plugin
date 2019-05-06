<?php

namespace GoSwoole\Plugins\EasyRoute\RouteTool;

use GoSwoole\BaseServer\Server\Beans\Request;
use GoSwoole\BaseServer\Server\Beans\Response;
use GoSwoole\Plugins\EasyRoute\ClientData;

/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午3:09
 */
interface IRoute
{
    public function handleClientData(ClientData $data);

    public function handleClientRequest(Request $request);

    public function getControllerName();

    public function getMethodName();

    public function getParams();

    public function getPath();
}