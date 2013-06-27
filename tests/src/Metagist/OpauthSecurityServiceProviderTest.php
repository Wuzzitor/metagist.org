<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the api controller.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class OpauthSecurityServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * 
     * @var OpauthSecurityServiceProvider
     */
    private $provider;
    
    /**
     * application mock
     * @var Application
     */
    private $application;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->application = new \Metagist\Application();
        
        $this->provider = new OpauthSecurityServiceProvider();
        $this->provider->register($this->application);
    }
    
    /**
     * Ensures the index action returns the routes.
     */
    public function testRegistersListener()
    {
        $this->application["security.authentication_providers"] = array(
            $this->getMock("\Silex\Provider\SecurityServiceProvider")
        );
        $this->application["users"] = $this->getMockBuilder("\Metagist\UserProvider")
            ->disableOriginalConstructor()
            ->getMock();
        $this->application["monolog"] = $this->getMock("\Psr\Log\LoggerInterface");
        
        $listener = $this->application[OpauthSecurityServiceProvider::LISTENER];
        $this->assertInstanceOf("\Metagist\OpauthListener", $listener);
    }
}