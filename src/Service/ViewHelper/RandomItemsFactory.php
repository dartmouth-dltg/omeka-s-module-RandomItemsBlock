<?php

namespace RandomItemsBlock\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use RandomItemsBlock\View\Helper\RandomItems;
use Zend\ServiceManager\Factory\FactoryInterface;

class RandomItemsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new RandomItems($services->get('Omeka\EntityManager'));
    }
}
