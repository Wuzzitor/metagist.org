<?php
namespace Metagist;

/**
 * Class representing the rating of a package by an user.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class Rating
{
    /**
     * user id
     * @var int|null
     */
    private $user_id;
    
    /**
     * user
     * @var User
     */
    private $user;
    
    /**
     * The time of the last update.
     * 
     * @var string
     */
    private $time_updated;
    
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
     * rating
     * @var int 
     */
    private $rating;
    
    /**
     * title
     * @var string 
     */
    private $title;
    
    /**
     * comment
     * @var string 
     */
    private $comment;
    
    /**
     * Factory method.
     * 
     * @param array $data
     * @return Rating
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
     * Returns the value.
     * 
     * @return string|int
     */
    public function getRating()
    {
        return $this->rating;
    }
    
    /**
     * Returns the title.
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Returns the comment text.
     * 
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
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
        if ($this->user_id != null) {
            return $this->user_id;
        }
        
        if ($this->user !== null) {
            return $this->user->getId();
        }
        
        return null;
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
     * Set the user.
     * 
     * @param \Metagist\User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
    
    /**
     * Returns the user.
     * 
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }
}