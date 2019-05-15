<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 13:42
 */

namespace ESD\Plugins\EasyRoute;


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
     * @var array
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
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param array|null $params
     */
    public function setParams(?array $params): void
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