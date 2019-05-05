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

/**
 * 不支持package_length_offset
 * Class LenJsonPack
 * @package GoSwoole\Plugins\EasyRoute\PackTool
 */
class LenJsonPack extends AbstractPack
{
    /**
     * 数据包编码
     * @param string $buffer
     * @return string
     * @throws PackException
     */
    public function encode(string $buffer)
    {
        $total_length = $this->getLength($this->portConfig->getPackageLengthType()) + strlen($buffer) - $this->portConfig->getPackageBodyOffset();
        return pack($this->portConfig->getPackageLengthType(), $total_length) . $buffer;
    }

    /**
     * @param $buffer
     * @return string
     * @throws PackException
     */
    public function decode(string $buffer)
    {
        return substr($buffer, $this->getLength($this->portConfig->getPackageLengthType()));
    }

    /**
     * @param $data
     * @param PortConfig $portConfig
     * @param null $topic
     * @return string
     * @throws PackException
     */
    public function pack($data, PortConfig $portConfig, $topic = null)
    {
        $this->portConfig = $portConfig;
        return $this->encode(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
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