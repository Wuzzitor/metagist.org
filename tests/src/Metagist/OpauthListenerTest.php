<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the opauth listener.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class OpauthListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var OpauthListener
     */
    private $listener;
    
    /**
     * client mock
     */
    private $securityContext;
    
    /**
     * event
     * @var \Symfony\Component\EventDispatcher\GenericEvent
     */
    private $genericEvent;
    
    /**
     * manager
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    private $manager;
    
    /**
     * provider
     * @var \Metagist\UserProvider
     */
    private $userProvider;
    
    /**
     * logger
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->securityContext = $this->getMockBuilder("\Symfony\Component\Security\Core\SecurityContext")
            ->disableOriginalConstructor()
            ->getMock();
        $this->genericEvent    = new \Symfony\Component\EventDispatcher\GenericEvent(array());
        $this->manager         = $this->getMock("\Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface");
        $this->userProvider    = $this->getMockBuilder("\Metagist\UserProvider")
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger          = $this->getMock("\Psr\Log\LoggerInterface");
        
        $this->listener = new OpauthListener($this->securityContext, $this->manager, $this->userProvider, $this->logger);
    }
    
    /**
     * Ensures onSucess call the required services.
     */
    public function testOnSucess()
    {
        $this->manager->expects($this->once())
            ->method("authenticate");
        $this->userProvider->expects($this->once())
            ->method("createUserFromOauthResponse")
            ->will($this->returnValue(new \Metagist\User('test', 'ROLE_ADMIN', 'http://an.url')));
        $this->securityContext->expects($this->once())
            ->method("setToken")
            ->with($this->isInstanceOf("Symfony\Component\Security\Core\Authentication\Token\TokenInterface"));
        
        $this->listener->onSuccess($this->genericEvent);
    }
    
    /**
     * Ensures onSucess redirects
     */
    public function testOnSucessRedirects()
    {
        $this->userProvider->expects($this->once())
            ->method("createUserFromOauthResponse")
            ->will($this->returnValue(new \Metagist\User('test', 'ROLE_ADMIN', 'http://an.url')));
        
        $this->listener->onSuccess($this->genericEvent);
        
        $this->assertInstanceOf(
            "\Symfony\Component\HttpFoundation\RedirectResponse",
            $this->genericEvent->getArgument('result')
        );
    }
    
    /**
     * Ensures the listener subscribed to success and error events.
     */
    public function testSubscribesToEvents()
    {
        $events = array(
            \Metagist\Auth\OpauthExtension::EVENT_SUCCESS => 'onSuccess',
            \Metagist\Auth\OpauthExtension::EVENT_ERROR => 'onError'
        );
        
        $this->assertEquals($events, OpauthListener::getSubscribedEvents());
    }
    
    /**
     * Tests the fake user creation for remote workers.
     */
    public function testOnWorkerAuthentication()
    {
        $this->manager->expects($this->once())
            ->method("authenticate");
        $this->securityContext->expects($this->once())
            ->method("setToken")
            ->with($this->isInstanceOf("Symfony\Component\Security\Core\Authentication\Token\TokenInterface"));
        
        $user = $this->listener->onWorkerAuthentication('aconsumer');
        $this->assertInstanceOf("\Metagist\User", $user);
        $this->assertContains(User::ROLE_SYSTEM, $user->getRoles());
    }
}