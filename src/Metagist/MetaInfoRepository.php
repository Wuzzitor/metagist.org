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
     * a meta info validator instance
     * @var Validator
     */
    private $validator;
    
    /**
     * Constructor.
     * 
     * @param \Doctrine\DBAL\Driver\Connection $connection
     * @param Validator                        $validator
     */
    public function __construct(Connection $connection, Validator $validator)
    {
        $this->connection = $connection;
        $this->validator  = $validator;
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
     * Returns a collection of metainfos with dummy packages.
     * 
     * @param string $category
     * @param string $group
     * @return \Doctrine\Common\Collections\Collection
     */
    public function byGroup($group)
    {
        if (!$this->validator->isValidGroup($group)) {
            throw new \InvalidArgumentException('Group not existing.');
        }
        
        $stmt = $this->connection->executeQuery(
            'SELECT m.*, p.identifier FROM metainfo m LEFT JOIN packages p ON p.id = m.package_id 
             WHERE `group` = ?',
            array($group)
        );
        $collection = new ArrayCollection();
        while ($row = $stmt->fetch()) {
            $collection->add($this->createMetaInfoWithDummyPackage($row));
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
            $this->save($info, null);
        }
    }
    
    /**
     * Saves (inserts) a single info.
     * 
     * @param \Metagist\MetaInfo $info
     * @param mixed              $cardinality
     * @return int
     */
    public function save(MetaInfo $info, $cardinality)
    {
        if ($cardinality === 1) {
            $this->connection->executeQuery(
                'DELETE FROM metainfo WHERE package_id = ? AND `group` = ?',
                array(
                    $info->getPackage()->getId(),
                    $info->getGroup()
                )
            );
        }
        
        $stmt = $this->connection->executeQuery(
            'INSERT INTO metainfo (package_id, user_id, time_updated, version, `group`, value) 
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            array(
                $info->getPackage()->getId(),
                $info->getUserId(),
                date('Y-m-d H:i:s', time()),
                $info->getVersion(),
                $info->getGroup(),
                $info->getValue()
            )
        );
        
        return $stmt->rowCount();
    }
    
    /**
     * Retrieves metainfo that has been updated lately.
     * 
     * @param int $limit
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @todo parameter binding did not work.
     */
    public function latest($limit = 25)
    {
        $stmt = $this->connection->executeQuery(
            'SELECT m.*, p.identifier FROM metainfo m LEFT JOIN packages p ON p.id = m.package_id 
             ORDER BY time_updated DESC LIMIT ' . $limit,
            array()
        );
        $collection = new ArrayCollection();
        while ($row = $stmt->fetch()) {
            $collection->add($this->createMetaInfoWithDummyPackage($row));
        }
        return $collection;
    }
    
    /**
     * Creates a MetaInfo instance with a dummy package based on the results
     * of a joined query.
     * 
     * @param array $data
     * @return MetaInfo
     */
    protected function createMetaInfoWithDummyPackage(array $data)
    {
        $package = new Package($data['identifier'], $data['package_id']);
        $data['package'] = $package;
        $metainfo = MetaInfo::fromArray($data);
        return $metainfo;
    }
}