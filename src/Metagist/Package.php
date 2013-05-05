<?php
namespace Metagist;

/**
 * Class representing a Composer package.
 * 
 */
class Package
{
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
     * Constructor.
     * 
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
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
     * toString returns the identifier.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->identifier;
    }
}