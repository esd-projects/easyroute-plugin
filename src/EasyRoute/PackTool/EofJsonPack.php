<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午2:43
 */

namespace Server\Pack;

namespace GoSwoole\Plugins\EasyRoute\PackTool;

use GoSwoole\BaseServer\Plugins\Logger\GetLogger;
use GoSwoole\BaseServer\Server\Config\PortConfig;
use GoSwoole\BaseServer\Server\Server;
use GoSwoole\Plugins\EasyRoute\ClientData;
use GoSwoole\Plugins\EasyRoute\PackException;

class EofJsonPack extends AbstractPack
{
    use GetLogger;
    protected $last_data = null;
    protected $last_data_result = null;

    /**
     * 数据包编码
     * @param $buffer
     * @return string
     */
    public function encode(string $buffer)
    {
        return $buffer . $this->portConfig->getPackageEof();
    }

    /**
     * 数据包解码
     * @param $buffer
     * @return string
     */
    public function decode(string $buffer)
    {
        $data = str_replace($this->portConfig->getPackageEof(), '', $buffer);
        return $data;
    }

    /**
     * 数据包打包
     * @param $data
     * @param PortConfig $portConfig
     * @param string|null $topic
     * @return string
     */
    public function pack($data, PortConfig $portConfig, ?string $topic = null)
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
    public function unPack(string $data, PortConfig $portConfig): ClientData
    {
        $this->portConfig = $portConfig;
        $value = json_decode($this->decode($data));
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
        Server::$instance->closeFd($fd);
    }
}