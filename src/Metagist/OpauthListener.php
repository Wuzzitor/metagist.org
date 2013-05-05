<?php

namespace Metagist;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Symfony\Component\EventDispatcher\GenericEvent;
use \Symfony\Component\Security\Core\SecurityContext;
use \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use \Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use \Symfony\Component\Security\Http\Firewall\ListenerInterface;
use \Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * 
 */
class OpauthListener implements EventSubscriberInterface, ListenerInterface
{
    /**
     * context
     * @var \Symfony\Component\Security\Core\SecurityContext 
     */
    private $context;
    
    private $manager;
    
    private $provider;
    
    /**
     * Constructor.
     * 
     * @param \Symfony\Component\Security\Core\SecurityContext $context
     * @param \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager $manager
     */
    public function __construct(
        SecurityContext $context,
        AuthenticationProviderManager $manager,
        UserProvider $provider
    ) {
        $this->context  = $context;
        $this->manager  = $manager;
        $this->provider = $provider;
    }
    
    /**
     * Reacts on successful opauth authentication.
     * 
     * @param \Symfony\Component\EventDispatcher\GenericEvent $event
     */
    public function onSuccess(GenericEvent $event)
    {
        /* @var $response array */
        $response = $event->getSubject();
        $user = $this->provider->createUserFromOauthResponse($response);
        
        $token = new PreAuthenticatedToken($user, '', 'opauth');
        $this->manager->authenticate($token);
        $this->context->setToken($token);
    }
    
    /**
     * Returns the events it subscribes to.
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \SilexOpauth\OpauthExtension::EVENT_SUCCESS => 'onSuccess'
        );
    }

    public function handle(GetResponseEvent $event)
    {
        
    }

}