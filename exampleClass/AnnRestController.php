<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/14
 * Time: 18:55
 */

namespace ESD\Plugins\EasyRoute\ExampleClass;

use ESD\Plugins\EasyRoute\Annotation\GetMapping;
use ESD\Plugins\EasyRoute\Annotation\ModelAttribute;
use ESD\Plugins\EasyRoute\Annotation\PathVariable;
use ESD\Plugins\EasyRoute\Annotation\PostMapping;
use ESD\Plugins\EasyRoute\Annotation\RequestBody;
use ESD\Plugins\EasyRoute\Annotation\RequestParam;
use ESD\Plugins\EasyRoute\Annotation\RestController;
use ESD\Plugins\EasyRoute\Controller\EasyController;

/**
 * @RestController("test")
 * Class TestController
 * @package ESD\Plugins\EasyRoute
 */
class AnnRestController extends EasyController
{
    /**
     * get请求
     * @GetMapping("/")
     * @return string
     */
    public function hello()
    {
        return "hello";
    }

    /**
     * get请求
     * @GetMapping("test/{name}")
     * @PathVariable("name")
     * @RequestParam("id")
     * @param $name
     * @param $id
     * @return string
     */
    public function test($name, $id)
    {
        var_dump($name, $id);
        return "test222";
    }

    /**
     * post RequestBody （json body）请求
     * @PostMapping("test2/{name}")
     * @PathVariable("name")
     * @RequestBody("test")
     * @param $name
     * @param $test
     * @return string
     */
    public function test2($name, TestPost $test)
    {
        var_dump($name, $test);
        return "test444";
    }

    /**
     * post 表单 请求
     * @PostMapping("test3/{name}")
     * @PathVariable("name")
     * @ModelAttribute("test")
     * @param $name
     * @param $test
     * @return string
     */
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