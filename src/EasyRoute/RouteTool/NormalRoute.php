<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午3:11
 */

namespace GoSwoole\Plugins\EasyRoute\RouteTool;


use GoSwoole\BaseServer\Plugins\Logger\GetLogger;
use GoSwoole\BaseServer\Server\Beans\Request;
use GoSwoole\BaseServer\Server\Beans\Response;
use GoSwoole\BaseServer\Server\Server;
use GoSwoole\Plugins\EasyRoute\ClientData;
use GoSwoole\Plugins\EasyRoute\RouteException;

class NormalRoute implements IRoute
{
    use GetLogger;
    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * 设置反序列化后的数据 Object
     * @param $data
     * @return ClientData
     * @throws RouteException
     */
    public function handleClientData(ClientData $data)
    {
        $this->clientData = $data;
        if (!empty($this->clientData->getControllerName()) && !empty($this->clientData->getMethodName())) {
            return $this->clientData;
        } else {
            throw new RouteException('route 数据缺少必要字段');
        }

    }

    /**
     * 处理http request
     * @param $request
     */
    public function handleClientRequest(Request $request)
    {
        $this->clientData = new ClientData();
        $this->clientData->setPath($request->getServer(Request::SERVER_PATH_INFO));
        $route = explode('/', $this->clientData->getPath());
        $count = count($route);
        if ($count == 2) {
            $this->clientData->setControllerName($route[$count - 1] ?? null);
            $this->clientData->setMethodName(null);
            return;
        }
        $this->clientData->setMethodName($route[$count - 1] ?? null);
        unset($route[$count - 1]);
        unset($route[0]);
        $this->clientData->setControllerName(implode("\\", $route));
    }

    /**
     * 获取控制器名称
     * @return string
     */
    public function getControllerName()
    {
        return $this->clientData->getControllerName();
    }

    /**
     * 获取方法名称
     * @return string
     */
    public function getMethodName()
    {
        return $this->clientData->getMethodName();
    }

    public function getPath()
    {
        return $this->clientData->getPath();
    }

    public function getParams()
    {
        return $this->clientData->getParams();
    }

    /**
     * @return ClientData
     */
    public function getClientData(): ClientData
    {
        return $this->clientData;
    }
}