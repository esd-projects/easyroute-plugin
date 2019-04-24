<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/24
 * Time: 16:06
 */

namespace GoSwoole\Route\ExampleClass;


use GoSwoole\Route\EasyRoute\Controller\EasyController;
use Monolog\Logger;

class TestController extends EasyController
{

    public function handle($values)
    {
        if (empty($values)) {
            return $this->index();
        } else {
            return $this->other($values['name']);
        }

    }

    private function index()
    {
        return "Index";
    }

    private function other($name)
    {
        $this->log(Logger::INFO, $name);
        return $name;
    }
}