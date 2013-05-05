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
     * @param PackagistClient $client
     */
    public function __construct(Connection $connection, PackagistClient $client = null)
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
        return new Package($author . '/' . $name);
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