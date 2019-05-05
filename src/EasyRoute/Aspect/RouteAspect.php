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
    protected function aroundRequest(MethodInvocation $invocation)
    {
        list($request, $response) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        if ($abstractServerPort instanceof AbstractServerPort) {
            $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getName()];
            $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
            $routeTool->handleClientRequest($request);
            $controllerName = $routeTool->getControllerName();
            $methodName = $easyRouteConfig->getMethodPrefix() . $routeTool->getMethodName();
            $controllerInstance = $this->getController($easyRouteConfig, $controllerName);
            $result = $controllerInstance->handle($methodName, $routeTool->getParams());
            $response->end($result);
        }
        $response->end("");
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
}