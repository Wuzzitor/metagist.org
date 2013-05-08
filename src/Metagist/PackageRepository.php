<?php
namespace Metagist;

use \Doctrine\DBAL\Connection;

/**
 * Repository for packages.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageRepository
{
    /**
     * db connection
     * @var \Doctrine\DBAL\Connection 
     */
    private $connection;
    
    /**
     * validator instance
     * @var Validator 
     */
    private $validator;
    
    /**
     * Constructor.
     * 
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection, Validator $validator)
    {
        $this->connection = $connection;
        $this->validator  = $validator;
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
        if (!$this->validator->isValidName($author) || !$this->validator->isValidName($name)) {
            throw new \InvalidArgumentException('The author or package name is invalid.');
        }
        
        $identifier = $author . '/' . $name;
        $stmt = $this->connection->executeQuery(
            'SELECT * FROM packages WHERE identifier = ?', array($identifier)
        );

        if (!$data = $stmt->fetch()) {
            return null;
        } else {
            $package = new Package($data['identifier'], $data['id']);
            $package->setDescription($data['description']);
            $package->setVersions(explode(',', $data['versions']));
        }
        
        return $package;
    }
}