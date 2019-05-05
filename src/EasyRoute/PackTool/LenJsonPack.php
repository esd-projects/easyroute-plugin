<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午2:43
 */

namespace GoSwoole\Plugins\EasyRoute\PackTool;

use GoSwoole\BaseServer\Plugins\Logger\GetLogger;
use GoSwoole\BaseServer\Server\Config\PortConfig;
use GoSwoole\BaseServer\Server\Server;
use GoSwoole\Plugins\EasyRoute\ClientData;
use GoSwoole\Plugins\EasyRoute\PackException;

/**
 * 不支持package_length_offset
 * Class LenJsonPack
 * @package GoSwoole\Plugins\EasyRoute\PackTool
 */
class LenJsonPack extends AbstractPack
{
    use GetLogger;

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
     * @param string|null $topic
     * @return string
     * @throws PackException
     */
    public function pack(string $data, PortConfig $portConfig, ?string $topic = null)
    {
        $this->portConfig = $portConfig;
        return $this->encode(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param $data
     * @param PortConfig $portConfig
     * @return ClientData
     * @throws PackException
     */
    public function unPack(string $data, PortConfig $portConfig): ClientData
    {
        $this->portConfig = $portConfig;
        $value = json_decode($this->decode($data),true);
        if (empty($value)) {
            throw new PackException('json unPack 失败');
        }
        $clientData = new ClientData();
        $clientData->setData($value);
        $clientData->setControllerName($value['c']);
        $clientData->setMethodName($value['m']);
        return $clientData;
    }

    public function errorHandle(\Throwable $e, int $fd)
    {
        $this->error($e);
        if (Server::$instance->isEstablished($fd)) {
            Server::$instance->wsDisconnect($fd);
        } else {
            Server::$instance->closeFd($fd);
        }
    }
}