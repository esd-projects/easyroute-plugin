<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/6/18
 * Time: 10:36
 */

namespace ESD\Plugins\EasyRoute\Filter;


use ESD\Plugins\Pack\ClientData;

class FilterManager
{
    /**
     * @var EveryFilterManager[]
     */
    protected $managers = [];

    public function __construct()
    {
        $this->managers[AbstractFilter::FILTER_PRE] = new EveryFilterManager();
        $this->managers[AbstractFilter::FILTER_PRO] = new EveryFilterManager();
        $this->managers[AbstractFilter::FILTER_ROUTE] = new EveryFilterManager();
    }

    public function addFilter(AbstractFilter $filter)
    {
        $this->managers[$filter->getType()]->addOrder($filter);
        $this->managers[$filter->getType()]->order();
    }

    public function filter($type, ClientData $clientData, $data = null): int
    {
        return $this->managers[$type]->filter($clientData, $data);
    }
}