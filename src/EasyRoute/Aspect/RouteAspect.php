<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 14:54
 */

namespace ESD\Plugins\EasyRoute\Aspect;


use ESD\BaseServer\Plugins\Logger\GetLogger;
use ESD\BaseServer\Server\AbstractServerPort;
use ESD\BaseServer\Server\Beans\Request;
use ESD\BaseServer\Server\Beans\Response;
use ESD\BaseServer\Server\Beans\WebSocketFrame;
use ESD\BaseServer\Server\Server;
use ESD\Plugins\EasyRoute\ClientData;
use ESD\Plugins\EasyRoute\Controller\IController;
use ESD\Plugins\EasyRoute\EasyRouteConfig;
use ESD\Plugins\EasyRoute\PackTool\IPack;
use ESD\Plugins\EasyRoute\RouteConfig;
use ESD\Plugins\EasyRoute\RouteException;
use ESD\Plugins\EasyRoute\RouteTool\IRoute;
use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;

class RouteAspect implements Aspect
{
    use GetLogger;
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
     * @var RouteConfig
     */
    protected $routeConfig;

    /**
     * RouteAspect constructor.
     * @param $easyRouteConfigs
     * @param $routeConfig
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct($easyRouteConfigs, $routeConfig)
    {
        $this->easyRouteConfigs = $easyRouteConfigs;
        foreach ($this->easyRouteConfigs as $easyRouteConfig) {
            if (!empty($easyRouteConfig->getPackTool())) {
                if (!isset($this->packTools[$easyRouteConfig->getPackTool()])) {
                    $className = $easyRouteConfig->getPackTool();
                    $this->packTools[$easyRouteConfig->getPackTool()] = Server::$instance->getContainer()->get($className);
                }
            }
            if (!isset($this->routeTools[$easyRouteConfig->getRouteTool()])) {
                $className = $easyRouteConfig->getRouteTool();
                $this->routeTools[$easyRouteConfig->getRouteTool()] = Server::$instance->getContainer()->get($className);
            }
        }
        $this->routeConfig = $routeConfig;
    }

    /**
     * around onHttpRequest
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \ESD\BaseServer\Exception
     * @throws \Throwable
     * @Around("within(ESD\BaseServer\Server\IServerPort+) && execution(public **->onHttpRequest(*))")
     */
    protected function aroundHttpRequest(MethodInvocation $invocation)
    {
        list($request, $response) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        if ($abstractServerPort instanceof AbstractServerPort && $response instanceof Response) {
            $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
            setContextValue("EasyRouteConfig", $easyRouteConfig);
            $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
            try {
                $clientData = new ClientData($request->fd,
                    $request->getServer(Request::SERVER_REQUEST_METHOD),
                    $request->getServer(Request::SERVER_PATH_INFO),
                    $request->getData());
                $clientData->setRequest($request);
                $clientData->setResponse($response);
                $result = $routeTool->handleClientData($clientData, $easyRouteConfig);
                if (!$result) return;
                $controllerInstance = $this->getController($easyRouteConfig, $routeTool->getControllerName());
                $result = $controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams());
                if (is_array($result) || is_object($result)) {
                    $result = json_encode($result, JSON_UNESCAPED_UNICODE);
                }
                $response->append($result);
            } catch (\Throwable $e) {
                try {
                    //这里的错误会移交给IndexController处理
                    $controllerInstance = $this->getController($easyRouteConfig, $this->routeConfig->getErrorControllerName());
                    $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());
                    $response->append($controllerInstance->onExceptionHandle($e));
                } catch (\Throwable $e) {
                    $this->warn($e);
                }
                throw $e;
            }
        }
        return;
    }

    /**
     * around onTcpReceive
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \ESD\BaseServer\Exception
     * @throws \Throwable
     * @Around("within(ESD\BaseServer\Server\IServerPort+) && execution(public **->onTcpReceive(*))")
     */
    protected function aroundTcpReceive(MethodInvocation $invocation)
    {
        list($fd, $reactorId, $data) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        if ($abstractServerPort instanceof AbstractServerPort) {
            $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
            setContextValue("EasyRouteConfig", $easyRouteConfig);
            $packTool = $this->packTools[$easyRouteConfig->getPackTool()];
            $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
            try {
                $clientData = $packTool->unPack($fd, $data, $easyRouteConfig);
                setContextValue("ClientData", $clientData);
                $result = $routeTool->handleClientData($clientData, $easyRouteConfig);
                if (!$result) return;
                $controllerInstance = $this->getController($easyRouteConfig, $routeTool->getControllerName());
                $result = $controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams());
                if ($result != null) {
                    Server::$instance->send($fd, $packTool->pack($result, $easyRouteConfig));
                }
            } catch (\Throwable $e) {
                try {
                    //这里的错误会移交给IndexController处理
                    $controllerInstance = $this->getController($easyRouteConfig, $this->routeConfig->getErrorControllerName());
                    $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());
                    $controllerInstance->onExceptionHandle($e);
                } catch (\Throwable $e) {
                    $this->warn($e);
                }
                throw $e;
            }
        }
        return;
    }

    /**
     * around onWsMessage
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \ESD\BaseServer\Exception
     * @throws \Throwable
     * @Around("within(ESD\BaseServer\Server\IServerPort+) && execution(public **->onWsMessage(*))")
     */
    protected function aroundWsMessage(MethodInvocation $invocation)
    {
        list($frame) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        if ($abstractServerPort instanceof AbstractServerPort && $frame instanceof WebSocketFrame) {
            $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
            setContextValue("EasyRouteConfig", $easyRouteConfig);
            $packTool = $this->packTools[$easyRouteConfig->getPackTool()];
            $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
            try {
                $clientData = $packTool->unPack($frame->getFd(), $frame->getData(), $easyRouteConfig);
                setContextValue("ClientData", $clientData);
                $result = $routeTool->handleClientData($clientData, $easyRouteConfig);
                if (!$result) return;
                $controllerInstance = $this->getController($easyRouteConfig, $routeTool->getControllerName());
                $result = $controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams());
                if ($result != null) {
                    Server::$instance->wsPush($frame->getFd(), $packTool->pack($result, $easyRouteConfig), $easyRouteConfig->getWsOpcode());
                }
            } catch (\Throwable $e) {
                try {
                    //这里的错误会移交给IndexController处理
                    $controllerInstance = $this->getController($easyRouteConfig, $this->routeConfig->getErrorControllerName());
                    $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());
                    $controllerInstance->onExceptionHandle($e);
                } catch (\Throwable $e) {
                    $this->warn($e);
                }
                throw $e;
            }
        }
        return;
    }

    /**
     * around onUdpPacket
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(ESD\BaseServer\Server\IServerPort+) && execution(public **->onUdpPacket(*))")
     * @throws \Throwable
     */
    protected function aroundUdpPacket(MethodInvocation $invocation)
    {
        list($data, $clientInfo) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        if ($abstractServerPort instanceof AbstractServerPort) {
            $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
            $packTool = $this->packTools[$easyRouteConfig->getPackTool()];
            $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
            try {
                setContextValue("EasyRouteConfig", $easyRouteConfig);
                $clientData = $packTool->unPack(-1, $data, $easyRouteConfig);
                $clientData->setUdpClientInfo($clientInfo);
                setContextValue("ClientData", $clientData);
                $result = $routeTool->handleClientData($clientData, $easyRouteConfig);
                if (!$result) return;
                $controllerInstance = $this->getController($easyRouteConfig, $routeTool->getControllerName());
                $controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams());
            } catch (\Throwable $e) {
                try {
                    //这里的错误会移交给IndexController处理
                    $controllerInstance = $this->getController($easyRouteConfig, $this->routeConfig->getErrorControllerName());
                    $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());
                    $controllerInstance->onExceptionHandle($e);
                } catch (\Throwable $e) {
                    $this->warn($e);
                }
                throw $e;
            }
        }
        return;
    }

    /**
     * @param EasyRouteConfig $easyRouteConfig
     * @param $controllerName
     * @return IController
     * @throws RouteException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function getController(EasyRouteConfig $easyRouteConfig, $controllerName)
    {
        if (!isset($this->controllers[$controllerName])) {
            if (class_exists($controllerName)) {
                $controller = Server::$instance->getContainer()->get($controllerName);
                if ($controller instanceof IController) {
                    $this->controllers[$controllerName] = $controller;
                    return $controller;
                } else {
                    throw new RouteException("类{$controllerName}应该继承IController");
                }
            } else {
                throw new RouteException("没有找到类$controllerName");
            }
        } else {
            return $this->controllers[$controllerName];
        }
    }

    /**
     * 增强send，可以根据不同协议转码发送
     * @param $fd
     * @param $data
     * @param null $topic
     * @return bool
     */
    public function autoBoostSend($fd, $data, $topic = null): bool
    {
        $clientInfo = Server::$instance->getClientInfo($fd);
        $easyRouteConfig = $this->easyRouteConfigs[$clientInfo->getServerPort()];
        $pack = $this->packTools[$easyRouteConfig->getPackTool()];
        $data = $pack->pack($data, $easyRouteConfig, $topic);
        if (Server::$instance->isEstablished($fd)) {
            return Server::$instance->wsPush($fd, $data, $easyRouteConfig->getWsOpcode());
        } else {
            return Server::$instance->send($fd, $data);
        }
    }
}