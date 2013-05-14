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
     * Set the metainfos.
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
     * @return \Doctrine\Common\Collections\Collection|null
     */
    public function getMetaInfos()
    {
        return $this->metaInfos;
    }
    
    /**
     * toString returns the identifier.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->identifier;
    }
}