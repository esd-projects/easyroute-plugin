<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/24
 * Time: 15:10
 */

namespace GoSwoole\Route\EasyRoute\Controller;


interface IController
{
    public function handle($values);
}