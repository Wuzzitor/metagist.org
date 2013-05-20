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
    public function render($template, array $variables = array())
    {
        return $this['twig']->render($template, $variables);
    }
    
    /**
     * Provides access to the session.
     * 
     * @return \Symfony\Component\HttpFoundation\Session\Session;
     */
    public function session()
    {
        return $this['session'];
    }
    
    /**
     * Provides access to the logger.
     * 
     * @return \Monolog\Logger
     */
    public function logger()
    {
        return $this['monolog'];
    }
    
    /**
     * Returns the package repository.
     * 
     * @return \Metagist\PackageRepository
     */
    public function packages()
    {
        return $this[RepoProvider::PACKAGE_REPO];
    }
    
    /**
     * Returns the metainfo repository.
     * 
     * @return \Metagist\MetaInfoRepository
     */
    public function metainfo()
    {
        return $this[RepoProvider::METAINFO_REPO];
    }
    
    /**
     * Returns the metainfo repository.
     * 
     * @return \Metagist\MetaInfoRepository
     */
    public function ratings()
    {
        return $this[RepoProvider::RATINGS_REPO];
    }
    
    /**
     * Returns the category schema representation.
     * 
     * @return \Metagist\CategorySchema
     */
    public function categories()
    {
        return $this[RepoProvider::CATEGORY_SCHEMA];
    }

    /**
     * Returns the security context.
     * 
     * @return Symfony\Component\Security\Core\SecurityContext
     */
    public function security()
    {
        return $this['security'];
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