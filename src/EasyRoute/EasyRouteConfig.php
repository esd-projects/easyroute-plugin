<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 15:16
 */

namespace GoSwoole\Plugins\EasyRoute;


class EasyRouteConfig
{
    /**
     * 是否直接aop注入
     * @var bool
     */
    private $autoAspect;

    private $routes = [];
    /**
     * @var bool
     */
    private $cacheDisabled = true;
    /**
     * @var string
     */
    private $cacheFile;

    public function __construct($autoAspect = true)
    {
        $this->autoAspect = $autoAspect;
    }

    /**
     * 添加路由
     * @param $httpMethod
     * @param $route
     * @param $className
     */
    public function addRoute($httpMethod, $route, $className)
    {
        $this->routes[] = [
            "httpMethod" => $httpMethod,
            "route" => $route,
            "className" => $className
        ];
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @return bool
     */
    public function isCacheDisabled(): bool
    {
        return $this->cacheDisabled;
    }

    /**
     * @param bool $cacheDisabled
     */
    public function setCacheDisabled(bool $cacheDisabled): void
    {
        $this->cacheDisabled = $cacheDisabled;
    }

    /**
     * @return string
     */
    public function getCacheFile()
    {
        return $this->cacheFile;
    }

    /**
     * @param string $cacheFile
     */
    public function setCacheFile(string $cacheFile): void
    {
        $this->cacheFile = $cacheFile;
    }

    /**
     * @return bool
     */
    public function isAutoAspect(): bool
    {
        return $this->autoAspect;
    }

    /**
     * @param bool $autoAspect
     */
    public function setAutoAspect(bool $autoAspect): void
    {
        $this->autoAspect = $autoAspect;
    }
}