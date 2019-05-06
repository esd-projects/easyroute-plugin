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
     * @param $methodName
     * @param $params
     * @return mixed
     * @throws \Throwable
     */
    public function handle(?string $methodName, ?array $params)
    {
        if (!is_callable([$this, $methodName]) || $methodName == null) {
            $callMethodName = 'defaultMethod';
        } else {
            $callMethodName = $methodName;
        }
        try {
            $result = $this->initialization($methodName);
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
     * @param $methodName
     * @return mixed
     */
    protected function initialization(string $methodName)
    {
        $this->clientData = getContextValue(ClientData::class);
        $this->easyRouteConfig = getContextValue(EasyRouteConfig::class);
        $this->request = getContextValue(Request::class);
        $this->response = getContextValue(Response::class);
    }

    /**
     * 处理异常
     * @param $e
     * @return mixed
     * @throws \Throwable
     */
    protected function onExceptionHandle(\Throwable $e)
    {
        //如果加载了Whoops插件，可以这里直接throw异常出去
        throw $e;
    }

    /**
     * 找不到方法时调用
     * @param $methodName
     * @return mixed
     */
    abstract protected function defaultMethod(string $methodName);
}