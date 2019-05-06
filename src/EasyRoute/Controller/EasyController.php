<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 15:30
 */

namespace GoSwoole\Plugins\EasyRoute\Controller;


use DI\Annotation\Inject;
use Monolog\Logger;

abstract class EasyController implements IController
{
    /**
     * @Inject()
     * @var Logger
     */
    protected $log;

    /**
     * 调用方法
     * @param $methodName
     * @param $params
     * @return mixed
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
        } catch (\Throwable $e) {
            return $this->onExceptionHandle($e);
        }
        try {
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
    abstract protected function initialization(string $methodName);

    /**
     * 处理异常
     * @param $e
     * @return mixed
     */
    protected function onExceptionHandle(\Throwable $e)
    {
        $this->log->error($e);
        return $e->getMessage();
    }

    /**
     * 找不到方法时调用
     * @param $methodName
     * @return mixed
     */
    abstract protected function defaultMethod(string $methodName);
}