<?php
namespace Metagist;

use \Doctrine\DBAL\Driver\Connection;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Repository for package meta information.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoRepository
{
    /**
     * db connection
     * @var \Doctrine\DBAL\Driver\Connection 
     */
    private $connection;
    
    /**
     * Constructor.
     * 
     * @param \Doctrine\DBAL\Driver\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * Retrieves all stored meta info for the given package.
     * 
     * @param \Metagist\Package $package
     * @return \Doctrine\Common\Collections\Collection
     */
    public function byPackage(Package $package)
    {
        $collection = new ArrayCollection();
        $stmt = $this->connection->executeQuery(
            'SELECT * FROM metainfo WHERE package_id = ?', array($package->getId())
        );
        while ($row = $stmt->fetch()) {
            $collection->add(MetaInfo::fromArray($row));
        }
        
        return $collection;
    }
}