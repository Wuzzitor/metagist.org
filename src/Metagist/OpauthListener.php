<?php

namespace Metagist;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Symfony\Component\EventDispatcher\GenericEvent;
use \Symfony\Component\Security\Core\SecurityContext;
use \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use \Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use \Symfony\Component\Security\Http\Firewall\ListenerInterface;
use \Symfony\Component\HttpKernel\Event\GetResponseEvent;
use \Psr\Log\LoggerInterface;

/**
 * Listener for successful opauth authentication.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class OpauthListener implements EventSubscriberInterface //, ListenerInterface
{
    /**
     * context
     * @var \Symfony\Component\Security\Core\SecurityContext 
     */
    private $context;
    
    /**
     * auth manager
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager
     */
    private $manager;
    
    /**
     * user provider
     * @var UserProvider
     */
    private $provider;
    
    /**
     * logger
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    
    /**
     * Constructor.
     * 
     * @param \Symfony\Component\Security\Core\SecurityContext $context
     * @param \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager $manager
     * @param UserProvider $provider
     */
    public function __construct(
        SecurityContext $context,
        AuthenticationProviderManager $manager,
        UserProvider $provider,
        LoggerInterface $logger = null
    ) {
        $this->context  = $context;
        $this->manager  = $manager;
        $this->provider = $provider;
        $this->logger   = $logger;
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
        $this->log("GitHub auth success:" . var_export($user, true));
    }
    
    public function onError(GenericEvent $event)
    {
        $response = $event->getSubject();
        $this->log(var_export($response, true));
    }
    
    /**
     * Returns the events it subscribes to.
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \SilexOpauth\OpauthExtension::EVENT_SUCCESS => 'onSuccess',
            \SilexOpauth\OpauthExtension::EVENT_ERROR => 'onError'
        );
    }

    public function handle(GetResponseEvent $event)
    {
        $event;
    }

    /**
     * info-logs a message.
     * 
     * @param string $message
     */
    protected function log($message)
    {
        if ($this->logger) {
            $this->logger->info($message);
        }
    }

}