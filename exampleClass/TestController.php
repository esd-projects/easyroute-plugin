<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 16:06
 */

namespace ESD\Plugins\EasyRoute\ExampleClass;


use ESD\Plugins\EasyRoute\Controller\EasyController;

class TestController extends EasyController
{

    /**
     * 每次请求都会调用
     * @param string|null $controllerName
     * @param string|null $methodName
     * @return mixed
     */
    public function initialization(?string $controllerName, ?string $methodName)
    {
        parent::initialization($controllerName,$methodName);
        $this->log->debug($methodName);
    }

    /**
     * 找不到方法时调用
     * @param $methodName
     * @return mixed
     */
    protected function defaultMethod(string $methodName)
    {
        return $methodName;
    }


    public function http_test()
    {
        return "test";
    }

    public function ws_test()
    {
        return "test";
    }
}