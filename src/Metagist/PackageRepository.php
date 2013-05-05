<?php
namespace Metagist;

use \Doctrine\DBAL\Driver\Connection;
use \Packagist\Api\Client as PackagistClient;

/**
 * Repository for packages.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageRepository
{
    /**
     * db connection
     * @var \Doctrine\DBAL\Driver\Connection 
     */
    private $connection;
    
    /**
     * packagist api client
     * @var \Packagist\Api\Client
     */
    private $client;
    
    /**
     * Constructor.
     * 
     * @param \Doctrine\DBAL\Driver\Connection $connection
     * @param PackagistClient                  $client
     */
    public function __construct(Connection $connection, PackagistClient $client)
    {
        $this->connection = $connection;
        $this->client     = $client;
    }
    
    /**
     * Retrieve a package by author and name.
     * 
     * @param string $author
     * @param string $name
     * @return Package|null
     */
    public function byAuthorAndName($author, $name)
    {
        if (!$this->isValidName($author) || !$this->isValidName($name)) {
            throw new \InvalidArgumentException('The author or package name is invalid.');
        }
        
        $identifier = $author . '/' . $name;
        $stmt = $this->connection->executeQuery(
            'SELECT * FROM packages WHERE identifier = ?', array($identifier)
        );

        if (!$data = $stmt->fetch()) {
            $package = $this->createPackageFromPackagist($identifier);
            $stmt = $this->connection->executeQuery(
                'INSERT INTO packages (identifier, description, versions) VALUES (?, ?, ?)', 
                array($package->getIdentifier(), $package->getDescription(), implode(',', $package->getVersions()))
            );
        } else {
            $package = new Package($data['identifier']);
            $package->setDescription($data['description']);
            $package->setVersions(explode(',', $data['versions']));
        }
        
        return $package;
    }
    
    /**
     * 
     * @param string $identifier
     * @throws Exception
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
        $versions = array();
        foreach ($packagistPackage->getVersions() as $version) {
            $versions[] = $version->getVersion();
        }
        $package->setVersions($versions);
        return $package;
    }
    
    /**
     * Validate a name (author or package name).
     * 
     * @return boolean
     */
    public function isValidName($name)
    {
        $pattern = '/^[a-zA-Z0-9\-\.]{2,128}$/i';
        return (bool) preg_match($pattern, $name);
    }
}