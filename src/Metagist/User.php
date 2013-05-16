<?php

namespace Metagist;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Metagist user.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class User implements UserInterface
{
    /**
     * user role
     * @var string
     */
    const ROLE_USER = 'ROLE_USER';
    
    /**
     * admin role
     * @var string
     */
    const ROLE_ADMIN = 'ROLE_ADMIN';
    
    /**
     * the username aka github login
     * @var string
     */
    private $username;
    
    /**
     * the role
     * @var string
     */
    private $role;
    
    /**
     * the avatar iamge url
     * @var string
     */
    private $avatarUrl;
    
    /**
     * Constructor.
     * 
     * @param string $username github login / nickname
     * @param string $role
     * @param string $avatarUrl
     */
    public function __construct($username, $role, $avatarUrl)
    {
        $this->username    = $username;
        $this->role        = $role;
        $this->avatarUrl   = $avatarUrl;
    }
    
    /**
     * Returns the user's role.
     * 
     * @return array
     */
    public function getRoles()
    {
        return array($this->role);
    }

    /**
     * Returns the username (at metagist and github).
     * 
     * @return string
     */
    public function getUsername()
    {
       return $this->username;
    }
    
    /**
     * Returns the avatar image url.
     * 
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }

    public function eraseCredentials()
    {
        
    }

    public function getPassword()
    {
        
    }
    
    public function getSalt()
    {
        
    }
    
    /**
     * toString returns the username.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getUsername();
    }
}