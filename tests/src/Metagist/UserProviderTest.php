<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the user provider.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var UserProvider 
     */
    private $provider;
    
    /**
     * connectio mock
     * @var \Doctrine\DBAL\Connection 
     */
    private $connection;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->connection = $this->getMockBuilder("\Doctrine\DBAL\Connection")
            ->disableOriginalConstructor()
            ->getMock();
        $this->provider = new UserProvider($this->connection, array());
    }
    
    /**
     * Ensures the provider implements the UserProviderInterface
     */
    public function testImplementsInterface()
    {
        $this->assertInstanceOf("Symfony\Component\Security\Core\User\UserProviderInterface", $this->provider);
    }
    
    /**
     * Ensures the connection is used to query the database.
     */
    public function testReturnsUser()
    {
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(array('username' => 'test', 'avatar_url' => 'http://ava.tar')));
        
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        
        $user = $this->provider->loadUserByUsername('test');
        $this->assertInstanceOf('Metagist\User', $user);
    }
    
    /**
     * Ensures a new user is created when someone logs in using oauth.
     */
    public function testCreateUserFromOauthResponse()
    {
        $response = array(
            'auth' => array(
                'raw' => array(
                    'login' => 'test123',
                    'avatar_url' => 'http://ava.tar'
                )
            )
        );
        
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));
        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(null));
        
        $this->connection->expects($this->at(0))
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        $this->connection->expects($this->at(1))
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        
        $user = $this->provider->createUserFromOauthResponse($response);
        $this->assertInstanceOf('Metagist\User', $user);
        $this->assertEquals('test123', $user->getUsername());
    }
    
    /**
     * Ensures the admin configuration is regarded.
     */
    public function testLoadAdmin()
    {
        $this->provider = new UserProvider($this->connection, array('admins' => 'test123'));
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(array('username' => 'test123', 'avatar_url' => 'http://ava.tar')));
        
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        
        $user = $this->provider->loadUserByUsername('test123');
        $this->assertContains(User::ROLE_ADMIN, $user->getRoles());
    }
    
    /**
     * Creates a statement mock, the provided HydratorMockStatement seems to be broken.
     * 
     * @param array $methods
     * @return Statement mock
     */
    protected function createMockStatement(array $methods = array('rowCount', 'fetch'))
    {
        return $this->getMock('stdClass', $methods);
    }
}