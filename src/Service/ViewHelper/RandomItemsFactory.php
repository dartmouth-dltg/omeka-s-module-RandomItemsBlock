<?php

namespace RandomItemsBlock\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use RandomItemsBlock\View\Helper\RandomItems;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RandomItemsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $entityManager = $services->get('Omeka\EntityManager');
        $apiAdapterManager = $services->get('Omeka\ApiAdapterManager');

        return new RandomItems($entityManager, $apiAdapterManager);
    }
}
