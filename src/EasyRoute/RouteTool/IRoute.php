<?php

namespace GoSwoole\Plugins\EasyRoute\RouteTool;

use GoSwoole\BaseServer\Server\Beans\Request;
use GoSwoole\Plugins\EasyRoute\ClientData;

/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午3:09
 */
interface IRoute
{
    function handleClientData(ClientData $data);

    function handleClientRequest(Request $request);

    function getControllerName();

    function getMethodName();

    function getParams();

    function getPath();
}