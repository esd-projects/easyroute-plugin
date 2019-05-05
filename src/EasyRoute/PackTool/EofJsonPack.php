<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午2:43
 */

namespace Server\Pack;

namespace GoSwoole\Plugins\EasyRoute\PackTool;

use GoSwoole\BaseServer\Server\Config\PortConfig;
use GoSwoole\Plugins\EasyRoute\PackException;

class EofJsonPack extends AbstractPack
{
    protected $last_data = null;
    protected $last_data_result = null;

    /**
     * 数据包编码
     * @param $buffer
     * @return string
     */
    public function encode($buffer)
    {
        return $buffer . $this->portConfig->getPackageEof();
    }

    /**
     * 数据包解码
     * @param $buffer
     * @return string
     */
    public function decode($buffer)
    {
        $data = str_replace($this->portConfig->getPackageEof(), '', $buffer);
        return $data;
    }

    /**
     * 数据包打包
     * @param $data
     * @param PortConfig $portConfig
     * @param null $topic
     * @return string|null
     */
    public function pack($data, PortConfig $portConfig, $topic = null)
    {
        $this->portConfig = $portConfig;
        return $this->encode(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 数据包解包
     * @param $data
     * @param PortConfig $portConfig
     * @return mixed
     * @throws PackException
     */
    public function unPack($data, PortConfig $portConfig)
    {
        $this->portConfig = $portConfig;
        $value = json_decode($this->decode($data));
        if (empty($value)) {
            throw new PackException('json unPack 失败');
        }
        return $value;
    }
}