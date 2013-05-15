<?php
namespace Metagist;

/**
 * Class representing the contents of the metainfo.json file.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class CategorySchema
{
    /**
     * identifer for the repo category and group
     * @var string
     */
    const REPOSITORY_IDENTIFIER = 'transparency/repository';
    
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
        if ($this->categories === null) {
            throw new \InvalidArgumentException('Invalid json passed?');
        }
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
        $this->assertGroupExists($category, $group);
        return $this->categories->$category->types->$group->type;
    }
    
    /**
     * Returns the categories, iterable.
     * 
     * @return object
     */
    public function getCategories()
    {
        return $this->categories;
    }
    
    /**
     * Returns the groups of a category.
     * 
     * @param string $category
     * @return type
     */
    public function getGroups($category)
    {
        $this->assertCategoryExists($category);
        return $this->categories->$category->types;
    }
    
    /**
     * Asserts a category exists.
     * 
     * @param string $category
     * @throws \InvalidArgumentException
     */
    protected function assertCategoryExists($category)
    {
        if (!isset($this->categories->$category)) {
            throw new \InvalidArgumentException('Unknown category: ' . $category);
        }
    }
    
    /**
     * Asserts a group exists.
     * 
     * @param string $category
     * @param string $group
     * @throws \InvalidArgumentException
     */
    protected function assertGroupExists($category, $group)
    {
        $this->assertCategoryExists($category);
        
        if (!isset($this->categories->$category->types->$group)) {
            throw new \InvalidArgumentException('Unknown group: ' . $group);
        }
    }
}