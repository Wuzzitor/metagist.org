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
    
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
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