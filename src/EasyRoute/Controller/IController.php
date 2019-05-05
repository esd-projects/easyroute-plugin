<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 15:12
 */

namespace GoSwoole\Plugins\EasyRoute\Controller;

interface IController
{
    public function handle(?string $methodName, ?array $params);
}