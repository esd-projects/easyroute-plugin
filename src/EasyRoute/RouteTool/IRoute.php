<?php

namespace ESD\Plugins\EasyRoute\RouteTool;

use ESD\BaseServer\Server\Beans\Request;
use ESD\BaseServer\Server\Beans\Response;
use ESD\Plugins\EasyRoute\ClientData;
use ESD\Plugins\EasyRoute\EasyRouteConfig;

/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午3:09
 */
interface IRoute
{
    public function handleClientData(ClientData $data, EasyRouteConfig $easyRouteConfig): bool;

    public function handleClientRequest(Request $request, Response $response, EasyRouteConfig $easyRouteConfig): bool;

    public function getControllerName();

    public function getMethodName();

    public function getParams();

    public function getPath();
}