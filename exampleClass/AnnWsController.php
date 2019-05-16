<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/14
 * Time: 18:55
 */

namespace ESD\Plugins\EasyRoute\ExampleClass;

use ESD\Plugins\EasyRoute\Annotation\WsController;
use ESD\Plugins\EasyRoute\Annotation\WsMapping;
use ESD\Plugins\EasyRoute\Controller\EasyController;

/**
 * @WsController("ws")
 * Class TestController
 * @package ESD\Plugins\EasyRoute
 */
class AnnWsController extends EasyController
{
    /**
     * @WsMapping()
     * @return string
     */
    public function hello()
    {
        return "hello";
    }

    /**
     * 找不到方法时调用
     * @param $methodName
     * @return mixed
     */
    protected function defaultMethod(?string $methodName)
    {
        // TODO: Implement defaultMethod() method.
    }
}