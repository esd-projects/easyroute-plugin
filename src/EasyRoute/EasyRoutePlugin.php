<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 14:42
 */

namespace GoSwoole\Plugins\EasyRoute;


use GoSwoole\BaseServer\Plugins\Logger\GetLogger;
use GoSwoole\BaseServer\Server\Config\PortConfig;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\Plugin\AbstractPlugin;
use GoSwoole\BaseServer\Server\Server;
use GoSwoole\Plugins\Aop\AopPlugin;
use GoSwoole\Plugins\EasyRoute\Aspect\RouteAspect;

class EasyRoutePlugin extends AbstractPlugin
{
    use GetLogger;
    /**
     * @var EasyRouteConfig[]
     */
    private $easyRouteConfigs = [];
    /**
     * @var RouteAspect
     */
    private $routeAspect;

    public function __construct()
    {
        parent::__construct();
        //需要aop的支持，所以放在aop后加载
        $this->atAfter(AopPlugin::class);
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "EasyRoute";
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \GoSwoole\BaseServer\Exception
     * @throws \GoSwoole\BaseServer\Server\Exception\ConfigException
     * @throws \ReflectionException
     */
    public function beforeServerStart(Context $context)
    {
        $serverConfig = $context->getServer()->getServerConfig();
        $configs = Server::$instance->getConfigContext()->get(PortConfig::key);
        foreach ($configs as $key => $value) {
            $easyRouteConfig = new EasyRouteConfig();
            if (empty($easyRouteConfig->getControllerNameSpace())) {
                $easyRouteConfig->setControllerNameSpace("GoSwoole\\Controllers");
            }
            $easyRouteConfig->setName($key);
            $easyRouteConfig->buildFromConfig($value);
            $easyRouteConfig->merge();
            $this->easyRouteConfigs[$easyRouteConfig->getPort()] = $easyRouteConfig;
        }
        //AOP注入
        $aopPlugin = $context->getServer()->getPlugManager()->getPlug(AopPlugin::class);
        if ($aopPlugin instanceof AopPlugin) {
            $aopPlugin->getAopConfig()->addIncludePath($serverConfig->getVendorDir() . "/go-swoole/base-server");
            $this->routeAspect = new RouteAspect($this->easyRouteConfigs);
            $aopPlugin->getAopConfig()->addAspect($this->routeAspect);
        } else {
            $this->warn("没有添加AOP插件，EasyRoute无法自动工作，需要手动配置入口");
        }
        $this->setToDIContainer(ClientData::class, new ClientDataProxy());
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }

    /**
     * @return RouteAspect
     */
    public function getRouteAspect(): RouteAspect
    {
        return $this->routeAspect;
    }
}