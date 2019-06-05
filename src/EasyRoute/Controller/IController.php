<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 15:12
 */

namespace ESD\Plugins\EasyRoute\Controller;

use ESD\Psr\Tracing\TracingInterface;

interface IController extends TracingInterface
{
    public function handle(?string $controllerName, ?string $methodName, ?array $params);

    public function initialization(?string $controllerName, ?string $methodName);

    public function onExceptionHandle(\Throwable $e);
}