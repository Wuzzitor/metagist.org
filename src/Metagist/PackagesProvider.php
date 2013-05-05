<?php
namespace Metagist;

/**
 * Provides the PackageRepository to the application.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackagesProvider implements \Silex\ServiceProviderInterface
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