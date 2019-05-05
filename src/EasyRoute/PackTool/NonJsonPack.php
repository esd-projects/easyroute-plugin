<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午2:43
 */

namespace GoSwoole\Plugins\EasyRoute\PackTool;

use GoSwoole\BaseServer\Server\Config\PortConfig;
use GoSwoole\Plugins\EasyRoute\PackException;

class NonJsonPack implements IPack
{
    /**
     * @param $data
     * @param PortConfig $portConfig
     * @param null $topic
     * @return false|string
     */
    public function pack($data, PortConfig $portConfig, $topic = null)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $data
     * @param PortConfig $portConfig
     * @return mixed
     * @throws PackException
     */
    public function unPack($data, PortConfig $portConfig)
    {
        $value = json_decode($data);
        if (empty($value)) {
            throw new PackException('json unPack 失败');
        }
        return $value;
    }

    public function encode($buffer)
    {
        return;
    }

    public function decode($buffer)
    {
        return;
    }
}