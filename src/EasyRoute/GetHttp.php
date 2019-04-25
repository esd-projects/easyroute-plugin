<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/25
 * Time: 15:44
 */

namespace GoSwoole\Plugins\EasyRoute;


use GoSwoole\BaseServer\Server\Beans\Request;
use GoSwoole\BaseServer\Server\Beans\Response;

trait GetHttp
{
    public function getRequest():Request
    {
       return getDeepContextValueByClassName(Request::class);
    }

    public function getResponse():Response
    {
        return getDeepContextValueByClassName(Response::class);
    }

}