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
     * pimple key under which the package factory can be accessed
     * @var string
     */
    const PACKAGE_FACTORY = 'factory.packages';
    
    /**
     * pimple key under which the metainfo repo can be accessed
     * @var string
     */
    const METAINFO_REPO = 'repo.metainfo';
    
    /**
     * pimple key under which the categories and groups can be accessed
     * @var string
     */
    const CATEGORY_SCHEMA = 'categories';
    
    /**
     * unused.
     * 
     * @param \Silex\Application $app
     */
    public function boot(\Silex\Application $app){}

    /**
     * Registers the PackageRepository.
     * 
     * @param \Silex\Application $app
     * @return void
     */
    public function register(\Silex\Application $app)
    {
        $json      = file_get_contents(__DIR__ . '/../../web/metainfo.json');
        $schema    = new CategorySchema($json);
        $validator = new Validator($schema);
        
        $app[self::CATEGORY_SCHEMA] = function () use ($schema) {
            return $schema;
        };
        
        $app[self::PACKAGE_REPO] = function () use ($app, $validator) {
            return new PackageRepository($app['db'], $validator);
        };
        
        $app[self::PACKAGE_FACTORY] = function () use ($app) {
            $client = new \Packagist\Api\Client();
            return new PackageFactory($client);
        };
        
        $app[self::METAINFO_REPO] = function () use ($app, $validator) {
            return new MetaInfoRepository($app['db'], $validator);
        };
    }
}