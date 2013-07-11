<?php
namespace Metagist;

use \Doctrine\Common\Collections\Collection;

/**
 * Class representing a Composer package.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class Package
{
    /**
     * internal id
     * @var int
     */
    protected $id;
    
    /**
     * package identifier (author + name)
     * @var string
     */
    protected $identifier;
    
    /**
     * version names
     * @var string[]
     */
    protected $versions = array();
    
    /**
     * package description
     * @var string
     */
    protected $description;
    
    /**
     * type of the package
     * @var string
     */
    protected $type;
    
    /**
     * datetime of the last update
     * @var string
     */
    protected $time_updated;
    
    /**
     * metainfos
     * @var Collection
     */
    protected $metaInfos;
    
    /**
     * Constructor.
     * 
     * @param string  $identifier
     * @param integer $id
     */
    public function __construct($identifier, $id = null)
    {
        $this->identifier = $identifier;
        $this->id         = (int) $id;  
    }
    
    /**
     * Returns the id of the package.
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set the id of the package.
     * 
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Returns the identifier of the package.
     * 
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    /**
     * Returns the author/owner part of the package identifier.
     * 
     * @return boolean
     */
    public function getAuthor()
    {
        if (!Validator::isValidIdentifier($this->identifier)) {
            return false;
        }
        $pieces = Util::splitIdentifier($this->identifier);
        return $pieces[0];
    }
    
    /**
     * Returns the name part of the package identifier.
     * 
     * @return string|false
     */
    public function getName()
    {
        if (!Validator::isValidIdentifier($this->identifier)) {
            return false;
        }
        $pieces = Util::splitIdentifier($this->identifier);
        return $pieces[1];
    }
    
    /**
     * Get the description.
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description.
     * 
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Set the known versions.
     * 
     * @param array $versions
     */
    public function setVersions(array $versions)
    {
        $this->versions = $versions;
    }
    
    /**
     * Returns all known versions.
     * 
     * @return string[]
     */
    public function getVersions()
    {
        return $this->versions;
    }
    
    /**
     * Type setter
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    
    /**
     * Returns the type of the package.
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set the metainfos and assigns the package to each metainfo.
     * 
     * @param \Doctrine\Common\Collections\Collection $collection
     */
    public function setMetaInfos(Collection $collection)
    {
        foreach ($collection as $metaInfo) {
            /* @var $metaInfo MetaInfo */
            $metaInfo->setPackage($this);
        }
        
        $this->metaInfos = $collection;
    }
    
    /**
     * Returns the associated metainfos.
     * 
     * @param string $group
     * @return \Doctrine\Common\Collections\Collection|null
     */
    public function getMetaInfos($group = null)
    {
        if ($group !== null) {
            $callback = function (MetaInfo $metainfo) use ($group) {
                return $metainfo->getGroup() == $group; 
            };
            return $this->metaInfos->filter($callback);
        }
        
        return $this->metaInfos;
    }
    
    /**
     * Returns the time of the last update
     * 
     * @return string|null
     */
    public function getTimeUpdated()
    {
        return $this->time_updated;
    }
    
    /**
     * Set the time of the last update
     * 
     * @return string|null
     */
    public function setTimeUpdated($time)
    {
        $this->time_updated = $time;
    }
    
    /**
     * toString returns the identifier.
     * 
     * @return string
     */
    public function __toString()
    {
        return substr($this->identifier, strpos($this->identifier, '/') + 1);
    }
}