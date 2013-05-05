<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the user class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var User
     */
    private $user;
    
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = new User('test123', User::ROLE_USER, 'http://an.url');
    }
    
    /**
     * Ensures the provider implements the UserProviderInterface
     */
    public function testGetUserName()
    {
        $this->assertEquals('test123', $this->user->getUsername());
    }
    
    public function testGetAvatarUrl()
    {
        $this->assertEquals('http://an.url', $this->user->getAvatarUrl());
    }
}