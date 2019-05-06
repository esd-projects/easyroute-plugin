<?php

namespace GoSwoole\Plugins\EasyRoute\PackTool;

use GoSwoole\BaseServer\Server\Config\PortConfig;
use GoSwoole\Plugins\EasyRoute\ClientData;

/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午2:41
 */
interface IPack
{
    public function encode(string $buffer);

    public function decode(string $buffer);

    public function pack(string $data, PortConfig $portConfig, ?string $topic = null);

    public function unPack(string $data, PortConfig $portConfig): ClientData;
}