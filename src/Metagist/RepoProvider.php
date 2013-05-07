<?php
namespace Metagist;

/**
 * Provides the repositories to the application.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class RepoProvider implements \Silex\ServiceProviderInterface
{
    /**
     * pimple key under which the package repo can be accessed
     * @var string
     */
    const PACKAGE_REPO = 'repo.packages';
    
    /**
     * pimple key under which the metainfo repo can be accessed
     * @var string
     */
    const METAINFO_REPO = 'repo.metainfo';
    
    public function boot(\Silex\Application $app){}

    /**
     * Registers the PackageRepository.
     * 
     * @param \Silex\Application $app
     * @return void
     */
    public function register(\Silex\Application $app)
    {
        $app[self::PACKAGE_REPO] = function () use ($app) {
            return new PackageRepository($app['db'], new \Packagist\Api\Client());
        };
        
        $app[self::METAINFO_REPO] = function () use ($app) {
            return new MetaInfoRepository($app['db']);
        };
    }
}