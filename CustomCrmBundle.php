<?php

namespace MauticPlugin\CustomCrmBundle;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\PluginBundle\Entity\Plugin;
use Mautic\CoreBundle\Factory\MauticFactory;

class CustomCrmBundle extends PluginBundleBase
{
    public function getParent()
    {
        return 'MauticLeadBundle';
    }

    static public function onPluginInstall(Plugin $plugin, MauticFactory $factory, $metadata = null)
    {
        if ($metadata !== null) {
            self::installPluginSchema($metadata, $factory);
        }

        // Do other install stuff
    }
}
