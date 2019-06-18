<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/6/18
 * Time: 11:18
 */

namespace ESD\Plugins\EasyRoute\Filter;


use ESD\Plugins\Pack\ClientData;
use ESD\Server\Co\Server;

class ServerFilter extends AbstractFilter
{

    public function getType()
    {
        return AbstractFilter::FILTER_PRE;
    }

    public function filter(ClientData $clientData): int
    {
        $clientData->getResponse()->withHeader('Server', Server::$instance->getServerConfig()->getName());
        return AbstractFilter::RETURN_NEXT;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "ServerFilter";
    }

    public function isEnable(ClientData $clientData)
    {
        return true;
    }
}