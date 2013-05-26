<?php

namespace Metagist;

use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Factory for MetaInfo objects.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoFactory
{
    /**
     * Creates metainfos based on a packagist package object.
     * 
     * @param \Packagist\Api\Result\Package $package
     * @return \Doctrine\Common\Collections\Collection
     */
    public function fromPackagistPackage(\Packagist\Api\Result\Package $package)
    {
        $metainfos     = array();
        $versions      = $package->getVersions();
        /* @var $firstVersion \Packagist\Api\Result\Package\Version */
        $firstVersion  = current($versions);
        
        if ($firstVersion != false) {
            $versionString = $firstVersion->getVersion();
            $metainfos = new ArrayCollection(
                array(
                    MetaInfo::fromValue(CategorySchema::REPOSITORY_IDENTIFIER, $package->getRepository(), $versionString),
                    MetaInfo::fromValue('reliability/maintainers', count($package->getMaintainers()), $versionString),
                    MetaInfo::fromValue('reliability/requires', count($firstVersion->getRequire()), $versionString),
                    MetaInfo::fromValue('reliability/requires.dev', count($firstVersion->getRequireDev()), $versionString)
                )
            );
            
            $licenses = $firstVersion->getLicense();
            if (is_array($licenses)) {
                $metainfos->add(MetaInfo::fromValue('documentation/license', implode(' ', $licenses), $versionString));
            }
        }
        
        return $metainfos;
    }
}
