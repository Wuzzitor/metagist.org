<?php
namespace Metagist;

use \Symfony\Component\Security\Core\Authentication\Provider\PreAuthenticatedAuthenticationProvider;
use \Symfony\Component\Security\Core\User\UserChecker;
use \Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

/**
 * OpauthSecurityServiceProvider for Silex to work with opauth.
 * 
 * This class is a copy of opauthSecurityServiceProvider by Benjamin Grandfond.
 *
 * @copyright (c) 2013, Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class OpauthSecurityServiceProvider extends \Silex\Provider\SecurityServiceProvider
{
    /**
     * key where the opauth listener is registered
     * 
     * @var string
     */
    const LISTENER = 'metagist.opauth.listener';
    
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(\Silex\Application $app)
    {
        parent::register($app);
        $this->registerFactory($app);
    }
    
    /**
     * Registers the opauth security factory.
     * 
     * @param \Silex\Application $app
     * @return array
     */
    protected function registerFactory(Application $app)
    {
        //opauth listener for event callbacks
        $app[self::LISTENER] = $app->share(
            function() use ($app) {
                return new OpauthListener(
                    $app['security'], 
                    $app['security.authentication_manager'],
                    $app['users'],
                    $app['monolog']
                );
            }
        );
        
        $app['security.authentication_listener.factory.opauth'] = $app->protect(function ($name, $options) use ($app) {
            // define the authentication provider object
            $app['security.authentication_provider.'.$name.'.opauth'] = $app->share(function () use ($app) {
                return new PreAuthenticatedAuthenticationProvider(
                    $app['users'],
                    new UserChecker(),
                    'opauth'
                );
            });

            // define the authentication listener object
            $app['security.authentication_listener.'.$name.'.opauth'] = $app->share(function () use ($app) {
                $subscriber = $app[\Metagist\OpauthSecurityServiceProvider::LISTENER];
                $dispatcher = $app['dispatcher'];
                /* @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcher */
                $dispatcher->addSubscriber($subscriber);
                return $subscriber;
            });

            return array(
                // the authentication provider id
                'security.authentication_provider.'.$name.'.opauth',
                // the authentication listener id
                'security.authentication_listener.'.$name.'.opauth',
                // the entry point id
                null,
                // the position of the listener in the stack
                'pre_auth'
            );
        });
    }
}