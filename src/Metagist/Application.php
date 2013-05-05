<?php
namespace Metagist;

/**
 * Metagist application.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class Application extends \Silex\Application
{
    /**
     * Shortcut to application['twig']->render.
     * 
     * @param string $template
     * @return string
     */
    public function render($template)
    {
        return $this['twig']->render($template);
    }
    
    /**
     * Provides access to the session.
     * 
     * @return Symfony\Component\HttpFoundation\Session\Session;
     */
    public function session()
    {
        return $this['session'];
    }
    
    /**
     * Provides access to the logger.
     * 
     * @return Monolog\Logger
     */
    public function logger()
    {
        return $this['logger'];
    }
    
    /**
     * Runs the application either from cache or not.
     * 
     * @return void
     */
    public function run()
    {
        if (isset($this['http_cache'])) {
            $this['http_cache']->run();
        } else {
            parent::run();
        }
    }
}