<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 13:42
 */

namespace GoSwoole\Plugins\EasyRoute;


class ClientData
{
    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $params;

    /**
     * @var array
     */
    protected $data;

    /**
     * @return string|null
     */
    public function getControllerName(): ?string
    {
        return $this->controllerName;
    }

    /**
     * @param string $controllerName
     */
    public function setControllerName(?string $controllerName): void
    {
        $this->controllerName = $controllerName;
    }

    /**
     * @return string|null
     */
    public function getMethodName(): ?string
    {
        return $this->methodName;
    }

    /**
     * @param string $methodName
     */
    public function setMethodName(?string $methodName): void
    {
        $this->methodName = $methodName;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string|null
     */
    public function getParams(): ?string
    {
        return $this->params;
    }

    /**
     * @param string $params
     */
    public function setParams(?string $params): void
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(?array $data): void
    {
        $this->data = $data;
    }
}