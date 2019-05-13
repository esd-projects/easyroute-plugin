<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/25
 * Time: 15:44
 */

namespace ESD\Plugins\EasyRoute;


use ESD\BaseServer\Server\Beans\Request;
use ESD\BaseServer\Server\Beans\Response;

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