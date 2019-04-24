<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 14:42
 */

namespace GoSwoole\Plugins\EasyRoute;


use FastRoute\RouteCollector;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\Plugin\AbstractPlugin;
use GoSwoole\Plugins\Aop\AopPlugin;
use GoSwoole\Plugins\EasyRoute\Aspect\RouteAspect;
use Monolog\Logger;
use function FastRoute\cachedDispatcher;

class EasyRoutePlugin extends AbstractPlugin
{
    /**
     * @var EasyRouteConfig
     */
    private $easyRouteConfig;
    /**
     * @var DispatchRoute
     */
    private $dispatchRoute;

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

    private function clear_dir($path = null)
    {
        if (is_dir($path)) {    //判断是否是目录
            $p = scandir($path);     //获取目录下所有文件
            foreach ($p as $value) {
                if ($value != '.' && $value != '..') {    //排除掉当./和../
                    if (is_dir($path . '/' . $value)) {
                        $this->clear_dir($path . '/' . $value);    //递归调用删除方法
                        rmdir($path . '/' . $value);    //删除当前文件夹
                    } else {
                        unlink($path . '/' . $value);    //删除当前文件
                    }
                }
            }
        }
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeServerStart(Context $context)
    {
        //有文件操作必须关闭全局RuntimeCoroutine
        enableRuntimeCoroutine(false);
        $log = $context->getDeepByClassName(Logger::class);
        if ($this->easyRouteConfig == null) {
            $log->warn("没有配置EasyRouteConfig");
            $this->easyRouteConfig = new EasyRouteConfig();
        }
        $serverConfig = $context->getServer()->getServerConfig();
        $cacheDir = $this->easyRouteConfig->getCacheFile() ?? $serverConfig->getBinDir() . "/cache/route";
        if (file_exists($cacheDir)) {
            $this->clear_dir($cacheDir);
            rmdir($cacheDir);
        }
        mkdir($cacheDir, 0777, true);
        $dispatcher = cachedDispatcher(function (RouteCollector $r) {
            foreach ($this->easyRouteConfig->getRoutes() as $route) {
                $r->addRoute($route['httpMethod'], $route['route'], $route['className']);
            }
        }, [
            'cacheFile' => $cacheDir,
            'cacheDisabled' => $this->easyRouteConfig->isCacheDisabled()
        ]);
        $this->dispatchRoute = new DispatchRoute($dispatcher);
        //AOP注入
        $aopPlugin = $context->getServer()->getPlugManager()->getPlug(AopPlugin::class);
        if ($aopPlugin instanceof AopPlugin) {
            $aopPlugin->getAopConfig()->addAspect(new RouteAspect($this->dispatchRoute));
        } else {
            $log->warn("没有添加AOP插件，EasyRoute无法自动工作，需要手动配置入口");
        }
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
     * @return EasyRouteConfig
     */
    public function getEasyRouteConfig(): EasyRouteConfig
    {
        return $this->easyRouteConfig;
    }

    /**
     * @param EasyRouteConfig $easyRouteConfig
     */
    public function setEasyRouteConfig(EasyRouteConfig $easyRouteConfig): void
    {
        $this->easyRouteConfig = $easyRouteConfig;
    }

    /**
     * @return DispatchRoute
     */
    public function getDispatchRoute(): DispatchRoute
    {
        return $this->dispatchRoute;
    }
}