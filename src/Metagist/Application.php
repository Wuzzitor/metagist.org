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
        /**
         * Icons, extensions must be registered very late
         * @link https://groups.google.com/forum/?fromgroups#!topic/silex-php/DzZNxSgCMFM
         */
        $this['twig']->addExtension(
            new \Metagist\Twig\IconExtension($this['category.icons.mapping'])
        );
        $this['twig']->addExtension(
            new \Metagist\Twig\MetaInfosExtension($this['category.render.mapping'])
        );
        
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
     * Returns the metainfo repository (proxy).
     * 
     * @return \Metagist\MetaInfoRepository
     */
    public function metainfo()
    {
        $proxy = new MetaInfoRepositoryProxy(
            $this[RepoProvider::METAINFO_REPO],
            $this->security(),
            $this->categories()
        );
        return $proxy;
    }
    
    /**
     * Returns the metainfo repository.
     * 
     * @return \Metagist\RatingRepository
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
     * @return \Symfony\Component\Security\Core\SecurityContext
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