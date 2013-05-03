<?php

namespace Metagist;

/**
 * Provides the contents of a json config file, which also contains the opauth
 * configuration.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com> 
 */
class ExternalConfigProvider implements \Silex\ServiceProviderInterface
{
    /**
     * pimple key where the path to the config is stored
     * @var string
     */
    const CONFIG_LOCATION = 'config.external.location';
    
    /**
     * pimple key where the config is stored
     * @var string
     */
    const CONFIG_KEY = 'config.external';
    
    public function boot(\Silex\Application $app)
    {
    }

    /**
     * Loads the configuration.
     * 
     * @param \Silex\Application $app
     */
    public function register(\Silex\Application $app)
    {
        $app[self::CONFIG_KEY] = json_decode(
            file_get_contents($app[self::CONFIG_LOCATION]),
            JSON_OBJECT_AS_ARRAY
        );
        
        $app['opauth'] = $app[self::CONFIG_KEY]['opauth'];
    }
}