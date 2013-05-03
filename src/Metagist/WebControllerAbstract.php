<?php

namespace Metagist;

use Silex\Application;

/**
 * Abstract controller which provides getters for dependencies.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
abstract class WebControllerAbstract
{
    /**
     * The Silex Application (pimple overlay).
     * @var \Silex\Application 
     */
    protected $application;

    /**
     * Constructor.
     * 
     * @param \Silex\Application $app
     */
    public function __construct(Application $app)
    {
        $this->application = $app;
        $this->initRoutes();
    }
    
    /**
     * Overwrite to set the routes.
     */
    abstract protected function initRoutes();
    
    /**
     * Shortcut to application['twig']->render.
     * 
     * @param string $template
     * @return string
     */
    protected function render($template)
    {
        return $this->application['twig']->render($template);
    }
    
    /**
     * Provides access to the session.
     * 
     * @return Symfony\Component\HttpFoundation\Session\Session;
     */
    protected function session()
    {
        return $this->application['session'];
    }
    
    /**
     * Provides access to the logger.
     * 
     * @return Monolog\Logger
     */
    protected function logger()
    {
        return $this->application['logger'];
    }
}