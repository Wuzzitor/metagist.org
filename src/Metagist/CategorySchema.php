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
     * string type
     * @var string
     */
    CONST TYPE_STRING = 'string';
    
    /**
     * boolean type
     * @var string
     */
    CONST TYPE_BOOLEAN = 'boolean';
    
    /**
     * integer type
     * @var string
     */
    CONST TYPE_INTEGER = 'integer';
    
    /**
     * url type
     * @var string
     */
    CONST TYPE_URL = 'url';
    
    /**
     * badge type (image url)
     * @var string
     */
    CONST TYPE_BADGE = 'badge';
    
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
     * @throws \InvalidArgumentException is the categories are null
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
     * Returns the role which can access the category or group.
     * 
     * @param string $category
     * @param string $group
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getAccess($category, $group = null)
    {
        if ($group === null) {
            $this->assertCategoryExists($category);
            return $this->categories->$category->access;
        }
        
        $this->assertGroupExists($category, $group);
        return $this->categories->$category->types->$group->access;
    }
    
    /**
     * Returns the categories, iterable.
     * 
     * @return array
     */
    public function getCategories()
    {
        return (array)$this->categories;
    }
    
    /**
     * Returns the groups of a category.
     * 
     * @param string $category
     * @return array
     */
    public function getGroups($category)
    {
        $this->assertCategoryExists($category);
        return (array)$this->categories->$category->types;
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