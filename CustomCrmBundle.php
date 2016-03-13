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
            foreach ($metadata as $key => $entity) {
                /** @var \Doctrine\ORM\Mapping\ClassMetadata $entity */
                if ($factory->getDatabase()->getSchemaManager()->tablesExist($entity->getTableName())) {
                    unset($metadata[$key]);
                }
            }

            self::installPluginSchema($metadata, $factory);
        }

        // Do other install stuff
    }
}
