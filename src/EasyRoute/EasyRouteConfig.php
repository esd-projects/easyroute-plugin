<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 15:16
 */

namespace GoSwoole\Plugins\EasyRoute;

use GoSwoole\BaseServer\Server\Config\PortConfig;
use GoSwoole\Plugins\EasyRoute\PackTool\LenJsonPack;
use GoSwoole\Plugins\EasyRoute\RouteTool\NormalRoute;

class EasyRouteConfig extends PortConfig
{
    /**
     * @var string
     */
    protected $controllerNameSpace = "";

    /**
     * @var string
     */
    protected $packTool = LenJsonPack::class;

    /**
     * @var string
     */
    protected $routeTool = NormalRoute::class;

    /**
     * @var string
     */
    protected $indexControllerName = "IndexController";

    /**
     * @var string
     */
    protected $methodPrefix = "";

    /**
     * @var string
     */
    protected $faviconPath = "";

    /**
     * @return string
     */
    public function getPackTool(): string
    {
        return $this->packTool;
    }

    /**
     * @param string $packTool
     */
    public function setPackTool(string $packTool): void
    {
        $this->packTool = $packTool;
    }

    /**
     * @return string
     */
    public function getRouteTool(): string
    {
        return $this->routeTool;
    }

    /**
     * @param string $routeTool
     */
    public function setRouteTool(string $routeTool): void
    {
        $this->routeTool = $routeTool;
    }

    /**
     * @return string
     */
    public function getMethodPrefix(): string
    {
        return $this->methodPrefix;
    }

    /**
     * @param string $methodPrefix
     */
    public function setMethodPrefix(string $methodPrefix): void
    {
        $this->methodPrefix = $methodPrefix;
    }

    /**
     * @return string
     */
    public function getControllerNameSpace(): string
    {
        return $this->controllerNameSpace;
    }

    /**
     * @param string $controllerNameSpace
     */
    public function setControllerNameSpace(string $controllerNameSpace): void
    {
        $this->controllerNameSpace = $controllerNameSpace;
    }

    /**
     * @return string
     */
    public function getFaviconPath(): string
    {
        return $this->faviconPath;
    }

    /**
     * @param string $faviconPath
     */
    public function setFaviconPath(string $faviconPath): void
    {
        $this->faviconPath = $faviconPath;
    }

    /**
     * @return string
     */
    public function getIndexControllerName(): string
    {
        return $this->indexControllerName;
    }

    /**
     * @param string $indexControllerName
     */
    public function setIndexControllerName(string $indexControllerName): void
    {
        $this->indexControllerName = $indexControllerName;
    }


}