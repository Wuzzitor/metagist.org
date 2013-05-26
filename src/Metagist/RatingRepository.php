<?php
namespace Metagist;

use \Doctrine\DBAL\Driver\Connection;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Repository for package ratings.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class RatingRepository
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
     * @param integer           $offset
     * @param integer           $limit
     * @return \Doctrine\Common\Collections\Collection
     */
    public function byPackage(Package $package, $offset = 0, $limit = 25)
    {
        $collection = new ArrayCollection();
        $stmt = $this->connection->executeQuery(
            'SELECT r.*, u.id AS user_id, u.username, u.avatar_url, p.identifier
             FROM ratings r
             LEFT JOIN packages p ON r.package_id = p.id
             LEFT JOIN users u ON r.user_id = u.id
             WHERE package_id = ? 
             ORDER BY time_updated DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset,
            array($package->getId())
        );
        while ($row = $stmt->fetch()) {
            $collection->add($this->createRatingWithDummyPackage($row));
        }
        
        return $collection;
    }
    
    /**
     * Retrieve the latest ratings.
     * 
     * @param int $limit
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function latest($limit = 1)
    {
        $collection = new ArrayCollection();
        $stmt = $this->connection->executeQuery(
            'SELECT r.*, u.id AS user_id, u.username, u.avatar_url, p.identifier
             FROM ratings r
             LEFT JOIN packages p ON r.package_id = p.id
             LEFT JOIN users u ON r.user_id = u.id
             ORDER BY time_updated DESC LIMIT ' . (int)$limit,
            array()
        );
        while ($row = $stmt->fetch()) {
            $collection->add($this->createRatingWithDummyPackage($row));
        }
        
        return $collection;
    }
    
    /**
     * Saves (inserts) a single info.
     * 
     * @param \Metagist\Rating $rating
     * @return int
     */
    public function save(Rating $rating)
    {
        $package = $rating->getPackage();
        $userId  = $rating->getUserId();
        
        if ($package == null || $package->getId() == null || $userId == null) {
            throw new \RuntimeException('Package ID and User ID must be set.');
        }
        $packageId = $package->getId();
        
        $this->connection->executeQuery(
            'DELETE FROM ratings WHERE (package_id = ? AND user_id = ?)',
            array($packageId, $userId)
        );
        
        $stmt = $this->connection->executeQuery(
            'INSERT INTO ratings (package_id, user_id, time_updated, version, rating, comment) 
             VALUES (?, ?, ?, ?, ?, ?)',
            array(
                $packageId,
                $userId,
                date('Y-m-d H:i:s', time()),
                $rating->getVersion(),
                $rating->getRating(),
                $rating->getComment()
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
    public function best($limit = 25)
    {
        $stmt = $this->connection->executeQuery(
            'SELECT AVG(r.rating) AS rating, r.package_id, p.identifier
             FROM ratings r 
             LEFT JOIN packages p ON p.id = r.package_id
             GROUP BY r.package_id
             ORDER BY rating DESC LIMIT ' . $limit,
            array()
        );
        $collection = new ArrayCollection();
        while ($row = $stmt->fetch()) {
            $collection->add($this->createRatingWithDummyPackage($row));
        }
        return $collection;
    }
    
    /**
     * Creates a Rating instance with a dummy package based on the results
     * of a joined query.
     * 
     * @param array $data
     * @return Rating
     */
    private function createRatingWithDummyPackage(array $data)
    {
        $package = new Package($data['identifier'], $data['package_id']);
        $data['package'] = $package;
        
        if (isset($data['username'])) {
            $user = new User($data['username'], 'ROLE_USER', $data['avatar_url']);
            $user->setId($data['user_id']);
            $data['user'] = $user;
        }
        
        $rating = Rating::fromArray($data);
        return $rating;
    }
}