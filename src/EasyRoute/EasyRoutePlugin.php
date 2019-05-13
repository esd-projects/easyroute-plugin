<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 14:42
 */

namespace ESD\Plugins\EasyRoute;


use ESD\BaseServer\Plugins\Logger\GetLogger;
use ESD\BaseServer\Server\Config\PortConfig;
use ESD\BaseServer\Server\Context;
use ESD\BaseServer\Server\Plugin\AbstractPlugin;
use ESD\BaseServer\Server\Server;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\EasyRoute\Aspect\RouteAspect;

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
     * @throws \ESD\BaseServer\Exception
     * @throws \ESD\BaseServer\Server\Exception\ConfigException
     * @throws \ReflectionException
     */
    public function beforeServerStart(Context $context)
    {
        $serverConfig = $context->getServer()->getServerConfig();
        $configs = Server::$instance->getConfigContext()->get(PortConfig::key);
        foreach ($configs as $key => $value) {
            $easyRouteConfig = new EasyRouteConfig();
            if (empty($easyRouteConfig->getControllerNameSpace())) {
                $easyRouteConfig->setControllerNameSpace("ESD\\Controllers");
            }
            $easyRouteConfig->setName($key);
            $easyRouteConfig->buildFromConfig($value);
            $easyRouteConfig->merge();
            $this->easyRouteConfigs[$easyRouteConfig->getPort()] = $easyRouteConfig;
        }
        //AOP注入
        $aopPlugin = $context->getServer()->getPlugManager()->getPlug(AopPlugin::class);
        if ($aopPlugin instanceof AopPlugin) {
            $aopPlugin->getAopConfig()->addIncludePath($serverConfig->getVendorDir() . "/esd/base-server");
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