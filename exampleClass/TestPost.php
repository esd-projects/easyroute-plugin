<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/15
 * Time: 13:48
 */

namespace ESD\Plugins\EasyRoute\ExampleClass;


use ESD\Plugins\Validate\Annotation\Filter;
use ESD\Plugins\Validate\Annotation\Validated;

class TestPost
{
    /**
     * @Validated(required=true);
     * @var string
     */
    public $one;
    /**
     * @Filter(snakeCase=true)
     * @Validated(required=true);
     * @var string
     */
    public $two;
}