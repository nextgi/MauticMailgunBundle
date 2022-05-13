<?php

namespace MauticPlugin\MauticMailgunBundle;

use MauticPlugin\MauticMailgunBundle\DependencyInjection\Compiler\EmailTransportPass;
use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MauticMailgunBundle.
 */
class MauticMailgunBundle extends PluginBundleBase
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EmailTransportPass());
    }
}
