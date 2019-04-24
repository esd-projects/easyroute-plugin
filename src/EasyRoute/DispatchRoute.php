<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 15:33
 */

namespace GoSwoole\Plugins\EasyRoute;


use FastRoute\Dispatcher;
use GoSwoole\BaseServer\Server\Beans\Request;
use GoSwoole\BaseServer\Server\Beans\Response;
use GoSwoole\Plugins\EasyRoute\Controller\IController;

class DispatchRoute
{
    private $controllerMaps = [];
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var callable
     */
    private $show404Handle;

    /**
     * @var callable
     */
    private $show405Handle;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->show404Handle = function (Response $response) {
            $response->setStatus(404);
            $response->addHeader("Content-Type", "text/html; charset=utf-8");
            $response->end("404");
        };
        $this->show405Handle = function (Response $response) {
            $response->setStatus(405);
            $response->addHeader("Content-Type", "text/html; charset=utf-8");
            $response->end("405");
        };
    }

    /**
     * 处理请求
     * @param Request $request
     * @param Response $response
     */
    public function handle(Request $request, Response $response)
    {
        $routeInfo = $this->dispatcher->dispatch($request->getServer(Request::SERVER_REQUEST_METHOD), $request->getServer(Request::SERVER_REQUEST_URI));
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                call_user_func($this->show404Handle, $response, $routeInfo);
                return;
            case Dispatcher::METHOD_NOT_ALLOWED:
                call_user_func($this->show405Handle, $response, $routeInfo);
                return;
            case Dispatcher::FOUND: // 找到对应的方法
                $className = $routeInfo[1];
                $vars = $routeInfo[2]; // 获取请求参数
                $controller = $this->getController($className);
                return $controller->handle($vars);
        }
    }

    private function getController($className): IController
    {
        if (!isset($this->controllerMaps[$className])) {
            $this->controllerMaps[$className] = new $className();
        }
        return $this->controllerMaps[$className];
    }

    /**
     * @param mixed $show404
     */
    public function setShow404Handle(callable $show404): void
    {
        $this->show404Handle = $show404;
    }

    /**
     * @param mixed $show405
     */
    public function setShow405Handle(callable $show405): void
    {
        $this->show405Handle = $show405;
    }
}