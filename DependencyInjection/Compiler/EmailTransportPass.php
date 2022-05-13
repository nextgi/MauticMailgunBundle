<?php

namespace MauticPlugin\MauticMailgunBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EmailTransportPass.
 */
class EmailTransportPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {

        // override the mautic transport type with
        // our custom transport type class definition
        // which includes mailgun definition
        $container
            ->register(
                'mautic.email.transport_type',
                'MauticPlugin\MauticMailgunBundle\Model\TransportType'
            );
    }
}
