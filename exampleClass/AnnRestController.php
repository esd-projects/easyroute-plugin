<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/14
 * Time: 18:55
 */

namespace ESD\Plugins\EasyRoute\ExampleClass;

use ESD\Plugins\EasyRoute\Annotation\RestController;
use ESD\Plugins\EasyRoute\Controller\EasyController;

/**
 * @RestController()
 * Class TestController
 * @package ESD\Plugins\EasyRoute
 */
class AnnRestController extends EasyController implements IAnnRestController
{

    public function hello()
    {
        return "hello";
    }

    public function test($name, $id)
    {
        var_dump($name, $id);
        return "test222";
    }


    public function test2($name, TestPost $test)
    {
        var_dump($name, $test);
        return "test444";
    }

    public function test3($name, TestPost $test)
    {
        var_dump($name, $test);
        return "test444";
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