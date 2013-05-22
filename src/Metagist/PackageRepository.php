<?php
namespace Metagist;

use \Doctrine\DBAL\Connection;
use \Doctrine\Common\Collections\ArrayCollection;

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
        }
        
        $package = $this->createPackageFromData($data);
        return $package;
    }
    
    /**
     * Retrieves all packages matching an identifier part.
     * 
     * @param string $identifier
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function byIdentifierPart($identifier)
    {
        $stmt = $this->connection->executeQuery(
            'SELECT * FROM packages WHERE identifier LIKE ?',
            array('%' . $identifier . '%')
        );

        $collection = new ArrayCollection();
        while ($data = $stmt->fetch()) {
            $collection->add($this->createPackageFromData($data));
        }
        
        return $collection;
    }
    
    /**
     * Saves a package.
     * 
     * @param \Metagist\Package $package
     * @return int
     */
    public function save(Package $package)
    {
        $id = $package->getId();
        $data = array(
            $package->getIdentifier(),
            $package->getDescription(),
            implode(',', $package->getVersions())
        );
        if ($id == null) {
            $stmt = $this->connection->executeQuery(
                'INSERT INTO packages (identifier, description, versions) VALUES (?, ?, ?)',
                $data
            );
            $id = $this->connection->lastInsertId();
            $package->setId($id);
        } else {
            $data[] = $id;
            $stmt = $this->connection->executeQuery(
                'UPDATE packages SET identifier = ?, description = ?, versions = ?)
                    WHERE id = ?',
                $data
            );
        }
        
        return $stmt->rowCount();
    }
    
    /**
     * Creates a package instance from fetched data.
     * 
     * @param array $data
     * @return \Metagist\Package
     */
    protected function createPackageFromData(array $data)
    {
        $package = new Package($data['identifier'], $data['id']);
        $package->setDescription($data['description']);
        $package->setVersions(explode(',', $data['versions']));
        
        return $package;
    }
}