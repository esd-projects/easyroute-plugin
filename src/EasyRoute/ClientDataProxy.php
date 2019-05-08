<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/8
 * Time: 10:03
 */

namespace GoSwoole\Plugins\EasyRoute;


class ClientDataProxy
{
    use GetClientData;
    public function __get($name)
    {
        return $this->getClientData()->$name;
    }

    public function __set($name, $value)
    {
        $this->getClientData()->$name = $value;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getClientData(), $name], $arguments);
    }
}