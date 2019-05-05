<?php

namespace GoSwoole\Plugins\EasyRoute\PackTool;

use GoSwoole\BaseServer\Server\Config\PortConfig;

/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午2:41
 */
interface IPack
{
    function encode(string $buffer);

    function decode(string $buffer);

    function pack($data, PortConfig $portConfig, $topic = null);

    function unPack($data, PortConfig $portConfig);
}