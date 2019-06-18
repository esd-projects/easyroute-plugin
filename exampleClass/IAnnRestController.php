<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/11
 * Time: 16:48
 */

namespace ESD\Plugins\EasyRoute\ExampleClass;

use ESD\Plugins\EasyRoute\Annotation\GetMapping;
use ESD\Plugins\EasyRoute\Annotation\ModelAttribute;
use ESD\Plugins\EasyRoute\Annotation\PathVariable;
use ESD\Plugins\EasyRoute\Annotation\PostMapping;
use ESD\Plugins\EasyRoute\Annotation\RequestBody;
use ESD\Plugins\EasyRoute\Annotation\RequestMapping;
use ESD\Plugins\EasyRoute\Annotation\RequestParam;

/**
 * @RequestMapping("test")
 * Interface IAnnRestController
 * @package ESD\Plugins\EasyRoute\ExampleClass
 */
interface IAnnRestController
{
    /**
     * get请求
     * @GetMapping("/")
     * @return string
     */
    public function hello();

    /**
     * get请求
     * @GetMapping("test/{name}")
     * @PathVariable("name")
     * @RequestParam("id")
     * @param $name
     * @param $id
     * @return string
     */
    public function test($name, $id);

    /**
     * post RequestBody （json body）请求
     * @PostMapping("test2/{name}")
     * @PathVariable("name")
     * @RequestBody("test")
     * @param $name
     * @param $test
     * @return string
     */
    public function test2($name, TestPost $test);

    /**
     * post 表单 请求
     * @PostMapping("test3/{name}")
     * @PathVariable("name")
     * @ModelAttribute("test")
     * @param $name
     * @param $test
     * @return string
     */
    public function test3($name, TestPost $test);
}