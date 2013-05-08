<?php
namespace Metagist;

use \Packagist\Api\Client as PackagistClient;

/**
 * Factory for packages (querying packagist).
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageFactory
{
    /**
     * packagist api client
     * @var \Packagist\Api\Client
     */
    private $client;
    
    /**
     * Constructor.
     * 
     * @param \Packagist\Api\Client $client
     */
    public function __construct(PackagistClient $client)
    {
        $this->client = $client;
    }
    
    /**
     * Fetches a package from packagist.
     * 
     * @param string $author
     * @param string $name
     * @return Package
     */
    public function byAuthorAndName($author, $name)
    {
        $identifier = $author . '/' . $name;
        $package = $this->createPackageFromPackagist($identifier);
        return $package;
    }
    
    /**
     * Creates an intermediate package by querying packagist.
     * 
     * @param string $identifier
     * @throws Exception
     * @return Package
     */
    protected function createPackageFromPackagist($identifier)
    {
        /* @var $packagistPackage \Packagist\Api\Result\Package */
        try {
            $packagistPackage = $this->client->get($identifier);
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $exception) {
            throw new Exception('Could not find ' . $identifier, Exception::PACKAGE_NOT_FOUND, $exception);
        }
        
        $package = new Package($packagistPackage->getName());
        $package->setDescription($packagistPackage->getDescription());
        $package->setType($packagistPackage->getType());
        
        //store version info
        $versions = array();
        foreach ($packagistPackage->getVersions() as $version) {
            $versions[] = $version->getVersion();
        }
        $package->setVersions($versions);
        
        $metainfos = new \Doctrine\Common\Collections\ArrayCollection(
            array(
                MetaInfo::fromValue(CategorySchema::REPOSITORY_IDENTIFIER, $packagistPackage->getRepository()),
            )
        );
        $package->setMetaInfos($metainfos);
        
        return $package;
    }
}