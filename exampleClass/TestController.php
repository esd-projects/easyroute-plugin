<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 16:06
 */

namespace GoSwoole\Plugins\EasyRoute\ExampleClass;


use GoSwoole\BaseServer\Plugins\Logger\GetLogger;
use GoSwoole\BaseServer\Server\Beans\Request;
use GoSwoole\Plugins\EasyRoute\Controller\EasyController;
use GoSwoole\Plugins\EasyRoute\GetHttp;
use Monolog\Logger;

class TestController extends EasyController
{
    use GetHttp;
    use GetLogger;

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
        $this->info($this->getRequest()->getServer(Request::SERVER_REQUEST_METHOD));
        return "Index";
    }

    private function other($name)
    {
        $this->log(Logger::INFO, $name);
        return $name;
    }
}