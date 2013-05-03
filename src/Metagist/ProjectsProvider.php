<?php

namespace Metagist;

/**
 * Provides the PackageRepository to the application.
 * 
 * 
 */
class ProjectsProvider implements \Silex\ServiceProviderInterface
{
    public function boot(\Silex\Application $app)
    {
        
    }

    /**
     * Registers the PackageRepository.
     * 
     * @param \Silex\Application $app
     * @return void
     */
    public function register(\Silex\Application $app)
    {
        $app['packagerepo'] = function () use ($app) {
            return new PackageRepository($app['db']);
        };
    }
}