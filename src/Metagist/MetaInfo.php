<?php
namespace Metagist;

/**
 * Class representing info on a package.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfo
{
    /**
     * category name
     * @var string
     */
    private $category;
    
    /**
     * group name
     * @var string
     */
    private $group;
    
    /**
     * Package 
     * @var Package 
     */
    private $package;
    
    /**
     * Content of the information.
     * @var string 
     */
    private $value;
    
    /**
     * Factory method.
     * 
     * @param array $data
     * @return MetaInfo
     */
    public static function fromArray(array $data)
    {
        $info = new self();
        foreach ($data as $key => $value) {
            if (!property_exists($info, $key)) {
                continue;
            }
            $info->$key = $value;
        }
        
        return $info;
    }
    
    /**
     * Returns the related package.
     * 
     * @return Package|null
     */
    public function getPackage()
    {
        return $this->package;
    }
    
    /**
     * Returns the category name.
     * 
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }
    
    /**
     * Returns the group name.
     * 
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }
    
    /**
     * Returns the value.
     * 
     * @return string|int
     */
    public function getValue()
    {
        return $this->value;
    }
}