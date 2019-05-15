<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午3:11
 */

namespace ESD\Plugins\EasyRoute\RouteTool;


use ESD\BaseServer\Plugins\Logger\GetLogger;
use ESD\BaseServer\Server\Beans\Request;
use ESD\BaseServer\Server\Beans\Response;
use ESD\Plugins\EasyRoute\ClientData;
use ESD\Plugins\EasyRoute\EasyRouteConfig;
use ESD\Plugins\EasyRoute\RouteException;

class NormalRoute implements IRoute
{
    use GetLogger;
    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * 设置反序列化后的数据 Object
     * @param ClientData $data
     * @param EasyRouteConfig $easyRouteConfig
     * @return bool
     * @throws RouteException
     */
    public function handleClientData(ClientData $data, EasyRouteConfig $easyRouteConfig): bool
    {
        $this->clientData = $data;
        if (!empty($this->clientData->getControllerName()) && !empty($this->clientData->getMethodName())) {
            return true;
        } else {
            throw new RouteException('route 数据缺少必要字段');
        }
        return true;
    }

    /**
     * 处理http request
     * @param Request $request
     * @param Response $response
     * @param EasyRouteConfig $easyRouteConfig
     * @return bool
     */
    public function handleClientRequest(Request $request, Response $response, EasyRouteConfig $easyRouteConfig): bool
    {
        $this->clientData = new ClientData();
        $this->clientData->setPath($request->getServer(Request::SERVER_PATH_INFO));
        $route = explode('/', $this->clientData->getPath());
        $count = count($route);
        if ($count == 2) {
            $controllerName = $route[$count - 1] ?? null;
            $methodName = null;
        }else {
            $methodName = $route[$count - 1] ?? null;
            unset($route[$count - 1]);
            unset($route[0]);
            $controllerName = implode("\\", $route);
        }

        if (strtolower($controllerName) == "favicon.ico") {
            if (is_file($easyRouteConfig->getFaviconPath())) {
                $response->addHeader("Content-Type", "image/x-icon");
                $response->sendfile($easyRouteConfig->getFaviconPath());
            } else {
                $response->end("");
            }
            return false;
        }

        if ($methodName != null) {
            $methodName = $easyRouteConfig->getMethodPrefix() . $methodName;
        }
        $this->clientData->setMethodName($methodName);
        $controllerName = ucfirst($controllerName);
        if (empty($controllerName)) {
            $controllerName = $easyRouteConfig->getIndexControllerName();
        }
        $className = $easyRouteConfig->getControllerNameSpace() . "\\" . $controllerName;
        $this->clientData->setControllerName($className);
        return true;
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