<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/6/18
 * Time: 10:36
 */

namespace ESD\Plugins\EasyRoute\Filter;


use ESD\Core\Order\OrderOwnerTrait;
use ESD\Plugins\Pack\ClientData;

class EveryFilterManager
{
    use OrderOwnerTrait;

    public function filter(ClientData $clientData): int
    {
        /** @var AbstractFilter $order */
        foreach ($this->orderList as $order) {
            if ($order->isEnable($clientData)) {
                $code = $order->filter($clientData);
                if ($code < AbstractFilter::RETURN_NEXT) return $code;
            }
        }
        return AbstractFilter::RETURN_END_FILTER;
    }
}