<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/14
 * Time: 16:58
 */

namespace ESD\Plugins\EasyRoute\Annotation;


use ESD\Plugins\AnnotationsScan\Annotation\Component;

/**
 * @Annotation
 * @Target("CLASS")
 * Class RestController
 * @package ESD\Plugins\EasyRoute\Annotation
 */
class RestController extends Component
{
    public $value = "";
}