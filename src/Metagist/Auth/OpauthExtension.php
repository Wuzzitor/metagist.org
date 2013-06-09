<?php
namespace Metagist\Auth;

use Opauth;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Fixes a closure issue.
 * 
 * @link https://github.com/mcrumm/silex-opauth/commit/f363714ee6955ea1e733360342f78c10214fc53e 
 */
class OpauthExtension implements ServiceProviderInterface
{
    /**
     * error event code
     * @var string
     */
    const EVENT_ERROR = 'opauth.error';
    
    /**
     * success event code
     * @var string
     */
    const EVENT_SUCCESS = 'opauth.success';
    
    /**
     * application instance
     * 
     * @var Application
     */
    protected $app;
    
    /**
     * configuration
     * 
     * @var array
     */
    protected $serviceConfig;
    
    /**
     * Registers the login callbacks.
     * 
     * @param \Silex\Application $app
     * @return void
     */
    public function register(Application $app) {
        $this->app = $app;
        $this->serviceConfig = $app['opauth'];
        $this->serviceConfig['config'] = array_merge(
                array(
                    'path' => $app['opauth']['login'] . '/',
                    'callback_url' => $app['opauth']['callback'], // Handy shortcut.
                    'callback_transport' => 'post' // Won't work with silex session
                ), $app['opauth']['config']
            );
        
        $that = $this;

        $app->match($this->serviceConfig['callback'], function() use ($that) {
          return $that->loginCallback();
        });
        
        $app->match($this->serviceConfig['login'] . '/{strategy}', function() use ($that) {
          return $that->loginAction();
        });
        $app->match($this->serviceConfig['login'] . '/{strategy}/{return}', function() use ($that) {
          return $that->loginAction();
        });
    }

    public function loginAction() {
        new Opauth($this->serviceConfig['config']);
        return '';
    }
    
    public function loginCallback() {
        $Opauth = new Opauth($this->serviceConfig['config'], false);

        $response = unserialize(base64_decode($_POST['opauth']));

        $failureReason = null;
        /**
         * Check if it's an error callback
         */
        if (array_key_exists('error', $response)) {
            return $this->onAuthenticationError($response['error'], $response);
        }

        /**
         * Auth response validation
         *
         * To validate that the auth response received is unaltered, especially auth response that
         * is sent through GET or POST.
         */ else {
            if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])) {
                return $this->onAuthenticationError('Missing key auth response components', $response);
            } elseif (!$Opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $failureReason)) {
                return $this->onAuthenticationError($failureReason, $response);
            } else {
                return $this->onAuthenticationSuccess($response);
            }
        }
        
        return '';
    }

    protected function onAuthenticationError($message, $response) {
        $e = new GenericEvent($response, array('message' => $message));
        $e->setArgument('result', '');
        return $this->app['dispatcher']->dispatch(self::EVENT_ERROR, $e)->getArgument('result');
    }
    
    protected function onAuthenticationSuccess($response) {
        $e = new GenericEvent($response);
        $e->setArgument('result', '');
        return $this->app['dispatcher']->dispatch(self::EVENT_SUCCESS, $e)->getArgument('result');
    }
    
    public function boot(Application $app) {
        
    }
}