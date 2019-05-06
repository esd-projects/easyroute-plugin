<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 15:30
 */

namespace GoSwoole\Plugins\EasyRoute\Controller;


use DI\Annotation\Inject;
use GoSwoole\BaseServer\Server\Beans\Request;
use GoSwoole\BaseServer\Server\Beans\Response;
use GoSwoole\Plugins\EasyRoute\ClientData;
use GoSwoole\Plugins\EasyRoute\EasyRouteConfig;
use GoSwoole\Plugins\EasyRoute\GetBoostSend;
use Monolog\Logger;

abstract class EasyController implements IController
{
    use GetBoostSend;

    /**
     * @Inject()
     * @var Logger
     */
    protected $log;

    /**
     * @var ClientData
     */
    protected $clientData;

    /**
     * @var EasyRouteConfig
     */
    protected $easyRouteConfig;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * 调用方法
     * @param string|null $controllerName
     * @param string|null $methodName
     * @param array|null $params
     * @return mixed
     * @throws \Throwable
     */
    public function handle(?string $controllerName, ?string $methodName, ?array $params)
    {
        if (!is_callable([$this, $methodName]) || $methodName == null) {
            $callMethodName = 'defaultMethod';
        } else {
            $callMethodName = $methodName;
        }
        try {
            $result = $this->initialization($controllerName, $methodName);
            if ($result != null) {
                return $result;
            }
            if ($params == null) {
                if ($callMethodName == "defaultMethod") {
                    return $this->defaultMethod($methodName);
                } else {
                    return call_user_func([$this, $callMethodName]);
                }
            } else {
                $params = array_values($params);
                return call_user_func_array([$this, $callMethodName], $params);
            }
        } catch (\Throwable $e) {
            return $this->onExceptionHandle($e);
        }
    }

    /**
     * 每次请求都会调用
     * @param string $controllerName
     * @param string $methodName
     * @return mixed
     */
    public function initialization(?string $controllerName, ?string $methodName)
    {
        $this->clientData = getDeepContextValueByClassName(ClientData::class);
        $this->easyRouteConfig = getDeepContextValueByClassName(EasyRouteConfig::class);
        $this->request = getDeepContextValueByClassName(Request::class);
        $this->response = getDeepContextValueByClassName(Response::class);
    }

    /**
     * 处理异常
     * @param $e
     * @return mixed
     * @throws \Throwable
     */
    public function onExceptionHandle(\Throwable $e)
    {
        $this->response->setStatus(404);
        $this->response->addHeader("Content-Type","text/html;charset=UTF-8");
        return $e->getMessage();
    }

    /**
     * 找不到方法时调用
     * @param $methodName
     * @return mixed
     */
    abstract protected function defaultMethod(string $methodName);
}