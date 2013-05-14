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
     * user id
     * @var int|null
     */
    private $user_id;
    
    /**
     * The time of the last update.
     * 
     * @var string
     */
    private $time_updated;
    
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
     * Version info
     * @var string 
     */
    private $version;
    
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
     * Factory method to create metainfo based on values.
     * 
     * @param string $identifier must be of format "$category/$group"
     * @param mixed  $value
     * @return MetaInfo
     */
    public static function fromValue($identifier, $value, $version = null)
    {
        list ($category, $group) = explode('/', $identifier);
        return self::fromArray(
            array(
                'category' => $category,
                'group'    => $group,
                'value'    => $value,
                'version'  => $version
            )
        );
    }

    /**
     * Set the related package.
     * 
     * @param Package $package
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;
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
    
    /**
     * Returns the associated version.
     * 
     * @return string|null
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * Returns the id of the user who created the info.
     * 
     * @return int|null
     */
    public function getUserId()
    {
        return $this->user_id;
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
}