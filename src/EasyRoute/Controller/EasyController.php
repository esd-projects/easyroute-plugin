<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 15:30
 */

namespace ESD\Plugins\EasyRoute\Controller;


use DI\Annotation\Inject;
use ESD\BaseServer\Server\Beans\Request;
use ESD\BaseServer\Server\Beans\Response;
use ESD\Plugins\Pack\ClientData;
use Monolog\Logger;

abstract class EasyController implements IController
{
    /**
     * @Inject()
     * @var Request
     */
    protected $request;
    /**
     * @Inject()
     * @var Response
     */
    protected $response;
    /**
     * @Inject()
     * @var ClientData
     */
    protected $clientData;
    /**
     * @Inject()
     * @var Logger
     */
    protected $log;

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
     * @param string|null $controllerName
     * @param string|null $methodName
     * @return mixed
     */
    public function initialization(?string $controllerName, ?string $methodName)
    {

    }

    /**
     * 处理异常
     * @param $e
     * @return mixed
     * @throws \Throwable
     */
    public function onExceptionHandle(\Throwable $e)
    {
        $this->response->addHeader("Content-Type", "text/html;charset=UTF-8");
        if($e instanceof  RouteException){
            if ($this->clientData->getResponse() != null) {
                $this->response->setStatus(404);
            }
            $msg = $e->getMessage();
        }else{
            $this->response->setStatus(500);
            $msg = 'http 500 internal server error';
        }
        return $msg;
    }

    /**
     * 找不到方法时调用
     * @param $methodName
     * @return mixed
     */
    abstract protected function defaultMethod(?string $methodName);
}