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
    
    /**
     * Saves a package.
     * 
     * @param \Metagist\Package $package
     * @throws \RuntimeException
     */
    public function savePackage(Package $package)
    {
        if ($package->getId() == null) {
            throw new \RuntimeException('Save the package first.');
        }
        
        //delete old entries
        $this->connection->executeQuery(
            'DELETE FROM metainfo WHERE package_id = ?',
            array($package->getId())
        );
            
        $metaInfos = $package->getMetaInfos();
        foreach ($metaInfos as $info) {
            $this->save($info);
        }
    }
    
    /**
     * Saves (inserts) a single info.
     * 
     * @param \Metagist\MetaInfo $info
     * @return int
     */
    public function save(MetaInfo $info)
    {
        $stmt = $this->connection->executeQuery(
            'INSERT INTO metainfo (package_id, user_id, time_updated, version, category, `group`, value) 
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            array(
                $info->getPackage()->getId(),
                $info->getUserId(),
                date('Y-m-d H:i:s', time()),
                $info->getVersion(),
                $info->getCategory(),
                $info->getGroup(),
                $info->getValue()
            )
        );
        
        return $stmt->rowCount();
    }
}