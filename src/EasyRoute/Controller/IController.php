<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 15:10
 */

namespace GoSwoole\Plugins\EasyRoute\Controller;


interface IController
{
    public function handle($values);
}