<?php
namespace Metagist;

/**
 * Class representing the contents of the metainfo.json file.
 * 
 * 
 */
class CategorySchema
{
    /**
     * @var object
     */
    private $categories;
    
    /**
     * Initialize with a json string.
     * 
     * @param string $json
     */
    public function __construct($json)
    {
        $this->categories = json_decode($json);
    }
    
    /**
     * Returns the type of a group.
     * 
     * @param string $category
     * @param string $group
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getType($category, $group)
    {
        if (!isset($this->categories->$category)) {
            throw new \InvalidArgumentException('Unknown category: ' . $category);
        }
        
        if (!isset($this->categories->$category->types->$group)) {
            throw new \InvalidArgumentException('Unknown group: ' . $group);
        }
        
        return $this->categories->$category->types->$group->type;
    }
}