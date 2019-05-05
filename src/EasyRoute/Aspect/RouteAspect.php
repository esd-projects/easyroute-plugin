<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 14:54
 */

namespace GoSwoole\Plugins\EasyRoute\Aspect;


use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;
use GoSwoole\BaseServer\Server\AbstractServerPort;
use GoSwoole\BaseServer\Server\Beans\Response;
use GoSwoole\BaseServer\Server\Beans\WebSocketFrame;
use GoSwoole\BaseServer\Server\Server;
use GoSwoole\Plugins\EasyRoute\Controller\IController;
use GoSwoole\Plugins\EasyRoute\EasyRouteConfig;
use GoSwoole\Plugins\EasyRoute\PackTool\IPack;
use GoSwoole\Plugins\EasyRoute\RouteException;
use GoSwoole\Plugins\EasyRoute\RouteTool\IRoute;

class RouteAspect implements Aspect
{
    /**
     * @var EasyRouteConfig[]
     */
    protected $easyRouteConfigs;
    /**
     * @var IPack[]
     */
    protected $packTools = [];
    /**
     * @var IRoute[]
     */
    protected $routeTools = [];

    /**
     * @var IController[]
     */
    protected $controllers = [];

    /**
     * RouteAspect constructor.
     * @param $easyRouteConfigs
     */
    public function __construct($easyRouteConfigs)
    {
        $this->easyRouteConfigs = $easyRouteConfigs;
        foreach ($this->easyRouteConfigs as $easyRouteConfig) {
            if (!empty($easyRouteConfig->getPackTool())) {
                if (!isset($this->packTools[$easyRouteConfig->getPackTool()])) {
                    $className = $easyRouteConfig->getPackTool();
                    $this->packTools[$easyRouteConfig->getPackTool()] = new $className();
                }
            }
            if (!isset($this->routeTools[$easyRouteConfig->getRouteTool()])) {
                $className = $easyRouteConfig->getRouteTool();
                $this->routeTools[$easyRouteConfig->getRouteTool()] = new $className();
            }
        }
    }

    /**
     * around onHttpRequest
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(GoSwoole\BaseServer\Server\IServerPort+) && execution(public **->onHttpRequest(*))")
     * @throws RouteException
     */
    protected function aroundHttpRequest(MethodInvocation $invocation)
    {
        list($request, $response) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        if ($abstractServerPort instanceof AbstractServerPort && $response instanceof Response) {
            $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getName()];
            $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
            try {
                $routeTool->handleClientRequest($request);
            } catch (\Throwable $e) {
                $routeTool->errorHttpHandle($e, $request, $response);
                $response->end("");
                return;
            }
            $controllerName = $routeTool->getControllerName();
            if (strtolower($controllerName) == "favicon.ico") {
                if (is_file($easyRouteConfig->getFaviconPath())) {
                    $response->addHeader("Content-Type", "image/x-icon");
                    $response->sendfile($easyRouteConfig->getFaviconPath());
                } else {
                    $response->end("");
                }
                return;
            }
            $methodName = $easyRouteConfig->getMethodPrefix() . $routeTool->getMethodName();
            $controllerInstance = $this->getController($easyRouteConfig, $controllerName);
            $result = $controllerInstance->handle($methodName, $routeTool->getParams());
            $response->end($result);
        }
        $response->end("");
        return;
    }

    /**
     * around onTcpReceive
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(GoSwoole\BaseServer\Server\IServerPort+) && execution(public **->onTcpReceive(*))")
     * @throws RouteException
     */
    protected function aroundTcpReceive(MethodInvocation $invocation)
    {
        list($fd, $reactorId, $data) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        if ($abstractServerPort instanceof AbstractServerPort) {
            $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getName()];
            $packTool = $this->packTools[$easyRouteConfig->getPackTool()];
            $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
            try {
                $clientData = $packTool->unPack($data, $easyRouteConfig);
            } catch (\Throwable $e) {
                $packTool->errorHandle($e, $fd);
                return;
            }
            try {
                $routeTool->handleClientData($clientData);
            } catch (\Throwable $e) {
                $routeTool->errorHandle($e, $fd);
                return;
            }
            $controllerName = $routeTool->getControllerName();
            $methodName = $easyRouteConfig->getMethodPrefix() . $routeTool->getMethodName();
            $controllerInstance = $this->getController($easyRouteConfig, $controllerName);
            $result = $controllerInstance->handle($methodName, $routeTool->getParams());
            if ($result != null) {
                Server::$instance->send($fd, $packTool->pack($result, $easyRouteConfig));
            }
        }
        return;
    }

    /**
     * around onWsMessage
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(GoSwoole\BaseServer\Server\IServerPort+) && execution(public **->onWsMessage(*))")
     * @throws RouteException
     */
    protected function aroundWsMessage(MethodInvocation $invocation)
    {
        list($frame) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        if ($abstractServerPort instanceof AbstractServerPort && $frame instanceof WebSocketFrame) {
            $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getName()];
            $packTool = $this->packTools[$easyRouteConfig->getPackTool()];
            $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
            try {
                $clientData = $packTool->unPack($frame->getData(), $easyRouteConfig);
            } catch (\Throwable $e) {
                $packTool->errorHandle($e, $frame->getFd());
                return;
            }
            try {
                $routeTool->handleClientData($clientData);
            } catch (\Throwable $e) {
                $routeTool->errorHandle($e, $frame->getFd());
                return;
            }
            $controllerName = $routeTool->getControllerName();
            $methodName = $easyRouteConfig->getMethodPrefix() . $routeTool->getMethodName();
            $controllerInstance = $this->getController($easyRouteConfig, $controllerName);
            $result = $controllerInstance->handle($methodName, $routeTool->getParams());
            if ($result != null) {
                Server::$instance->wsPush($frame->getFd(), $packTool->pack($result, $easyRouteConfig), $easyRouteConfig->getWsOpcode());
            }
        }
        return;
    }

    /**
     * around onUdpPacket
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(GoSwoole\BaseServer\Server\IServerPort+) && execution(public **->onUdpPacket(*))")
     * @throws RouteException
     */
    protected function aroundUdpPacket(MethodInvocation $invocation)
    {
        list($data, $clientInfo) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        if ($abstractServerPort instanceof AbstractServerPort) {
            $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
            $packTool = $this->packTools[$easyRouteConfig->getPackTool()];
            $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
            $clientData = $packTool->unPack($data, $easyRouteConfig);
            $routeTool->handleClientData($clientData);
            $controllerName = $routeTool->getControllerName();
            $methodName = $easyRouteConfig->getMethodPrefix() . $routeTool->getMethodName();
            $controllerInstance = $this->getController($easyRouteConfig, $controllerName);
            $controllerInstance->handle($methodName, $routeTool->getParams());
        }
        return;
    }

    /**
     * @param EasyRouteConfig $easyRouteConfig
     * @param $controllerName
     * @return IController
     * @throws RouteException
     */
    private function getController(EasyRouteConfig $easyRouteConfig, $controllerName)
    {
        $controllerName = ucfirst($controllerName);
        $className = $easyRouteConfig->getControllerNameSpace() . "\\" . $controllerName;
        if (!isset($this->controllers[$className])) {
            if (class_exists($className)) {
                $controller = new $className;
                if ($controller instanceof IController) {
                    $this->controllers[$className] = $controller;
                    return $controller;
                } else {
                    throw new RouteException("类{$className}应该继承IController");
                }
            } else {
                throw new RouteException("没有找到类$className");
            }
        } else {
            return $this->controllers[$className];
        }
    }

    /**
     * 增强send，可以根据不同协议转码发送
     * @param $fd
     * @param $data
     * @return bool
     */
    public function autoBoostSend($fd, $data): bool
    {
        $clientInfo = Server::$instance->getClientInfo($fd);
        $easyRouteConfig = $this->easyRouteConfigs[$clientInfo->getServerPort()];
        $pack = $this->packTools[$easyRouteConfig->getPackTool()];
        $data = $pack->pack($easyRouteConfig, $data);
        if (Server::$instance->isEstablished($fd)) {
            return Server::$instance->wsPush($fd, $data, $easyRouteConfig->getWsOpcode());
        } else {
            return Server::$instance->send($fd, $data);
        }
    }
}