<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 14:42
 */

namespace ESD\Plugins\EasyRoute;


use ESD\BaseServer\Server\Config\PortConfig;
use ESD\BaseServer\Server\Context;
use ESD\BaseServer\Server\Plugin\AbstractPlugin;
use ESD\BaseServer\Server\PlugIn\PluginInterfaceManager;
use ESD\BaseServer\Server\Server;
use ESD\Plugins\AnnotationsScan\AnnotationsScanPlugin;
use ESD\Plugins\AnnotationsScan\ScanClass;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\EasyRoute\Annotation\RequestMapping;
use ESD\Plugins\EasyRoute\Annotation\RestController;
use ESD\Plugins\EasyRoute\Aspect\RouteAspect;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\cachedDispatcher;

class EasyRoutePlugin extends AbstractPlugin
{
    public static $instance;
    /**
     * @var EasyRouteConfig[]
     */
    private $easyRouteConfigs = [];
    /**
     * @var RouteAspect
     */
    private $routeAspect;
    /**
     * @var Dispatcher
     */
    private $dispatcher;
    /**
     * @var ScanClass
     */
    private $scanClass;

    public function __construct()
    {
        parent::__construct();
        //需要aop的支持，所以放在aop后加载
        $this->atAfter(AnnotationsScanPlugin::class);
        self::$instance = $this;
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
     * @param Context $context
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\BaseServer\Exception
     * @throws \ReflectionException
     */
    public function init(Context $context)
    {
        parent::init($context);
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
        $serverConfig = $context->getServer()->getServerConfig();
        $aopConfig = Server::$instance->getContainer()->get(AopConfig::class);
        $aopConfig->addIncludePath($serverConfig->getVendorDir() . "/esd/base-server");
        $this->routeAspect = new RouteAspect($this->easyRouteConfigs);
        $aopConfig->addAspect($this->routeAspect);
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \ESD\BaseServer\Exception
     * @throws \ReflectionException
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlug(new AopPlugin());
        $pluginInterfaceManager->addPlug(new AnnotationsScanPlugin());
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\BaseServer\Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->setToDIContainer(ClientData::class, new ClientDataProxy());
        $this->scanClass = Server::$instance->getContainer()->get(ScanClass::class);
        $reflectionMethods = $this->scanClass->findMethodsByAnn(RequestMapping::class);
        $this->dispatcher = cachedDispatcher(function (RouteCollector $r) use ($reflectionMethods) {
            foreach ($reflectionMethods as $reflectionMethod) {
                $reflectionClass = $reflectionMethod->getDeclaringClass();
                $route = "/";
                $restController = $this->scanClass->getCachedReader()->getClassAnnotation($reflectionClass, RestController::class);
                if ($restController instanceof RestController) {
                    $restController->value = trim($restController->value, "/");
                    $route .= $restController->value;
                }
                $requestMapping = $this->scanClass->getCachedReader()->getMethodAnnotation($reflectionMethod, RequestMapping::class);
                if ($requestMapping instanceof RequestMapping) {
                    $requestMapping->value = trim($requestMapping->value, "/");
                    if (!empty($requestMapping->value)) {
                        $route .= "/" . $requestMapping->value;
                    }
                    foreach ($requestMapping->method as $method) {
                        Server::$instance->getLog()->debug("Mapping $method $route to $reflectionClass->name::$reflectionMethod->name");
                        $r->addRoute(strtoupper($method), $route, [$reflectionClass, $reflectionMethod]);
                    }
                }
            }
        }, [
            'cacheFile' => Server::$instance->getServerConfig()->getCacheDir() . "/route", /* required 缓存文件路径，必须设置 */
            'cacheDisabled' => Server::$instance->getServerConfig()->isDebug()  /* optional, enabled by default 是否缓存，可选参数，默认情况下开启 */
        ]);
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

    /**
     * @return Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @return ScanClass
     */
    public function getScanClass(): ScanClass
    {
        return $this->scanClass;
    }
}