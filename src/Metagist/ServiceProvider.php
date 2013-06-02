<?php
namespace Metagist;

/**
 * Provides the repositories to the application.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ServiceProvider implements \Silex\ServiceProviderInterface
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
     * pimple key under which the metainfo factory can be accessed
     * @var string
     */
    const METAINFO_FACTORY = 'factory.metainfo';
    
    /**
     * pimple key under which the ratings repo can be accessed
     * @var string
     */
    const RATINGS_REPO = 'repo.ratings';
    
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
        
        $app[self::METAINFO_FACTORY] = function () use ($app) {
            $client = new \Github\Client();
            
            $credentials = $app["opauth"]["config"]["Strategy"]["Github"];
            $client->authenticate(
                $credentials["client_id"],
                $credentials["client_secret"],
                \Github\Client::AUTH_URL_CLIENT_ID
            );
            $client->setHeaders(
                array("User-Agent" => "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0")
            );
            
            $factory = new MetaInfoFactory($app['monolog']);
            $factory->setGitHubClient($client);
            
            return $factory;
        };
        
        $app[self::PACKAGE_FACTORY] = function () use ($app) {
            $client = new \Packagist\Api\Client();
            return new PackageFactory($client, $app[self::METAINFO_FACTORY]);
        };
        
        $app[self::METAINFO_REPO] = function () use ($app, $validator) {
            return new MetaInfoRepository($app['db'], $validator);
        };
        
        $app[self::RATINGS_REPO] = function () use ($app) {
            return new RatingRepository($app['db']);
        };
    }
}