<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/6/13
 * Time: 16:54
 */

namespace ESD\Plugins\EasyRoute\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class MqttMapping extends RequestMapping
{
    public $method = ["mqtt"];
}