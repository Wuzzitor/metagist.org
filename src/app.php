<?php
/**
 * Metagist.org application bootstrapping
 * 
 * 
 */
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use SilexAssetic\AsseticServiceProvider;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Translation\Loader\YamlFileLoader;

/*
 * configuration 
 */
$env = getenv('APP_ENV') ? : 'dev';
require __DIR__ . "/../config/$env.php";

if ($app['http_cache.enabled'] == true) {
    $app->register(new HttpCacheServiceProvider());
}

$app->register(new SessionServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new UrlGeneratorServiceProvider());

/**
 * Use the metagist UserProvider
 * @link http://silex.sensiolabs.org/doc/providers/security.html#defining-a-custom-user-provider
 */
$app['users'] = $app->share(function () use ($app) {
    return new Metagist\UserProvider($app['db'], $app['security.users']);
});

$contributePath = '^/contribute';
$ratePath       = '^/rate';
$app->register(new Metagist\OpauthSecurityServiceProvider(), array(
    'security.firewalls' => array(
        'default' => array(
            'pattern' => '^/',
            'anonymous' => true,
            'opauth' => true,
            'users'  => function () use ($app) {return $app['users'];}
        ),
        'contribute' => array(
            'pattern' => $contributePath,
            'security' => true,
            'opauth' => true,
            'users'  => function () use ($app) {return $app['users'];}
        ),
        'rate' => array(
            'pattern' => $ratePath,
            'opauth' => true,
            'users'  => function () use ($app) {return $app['users'];}
        ),
    ),
    'security.role_hierarchy' => array(
        'ROLE_SYSTEM' => array('ROLE_USER'),
        'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_SYSTEM'),
    ),
    'security.access_rules' => array(
        array($contributePath, 'ROLE_USER'),
        array($ratePath, 'ROLE_USER'),
    )
));

$app['security.encoder.digest'] = $app->share(function ($app) {
        return new PlaintextPasswordEncoder();
    });

$app->register(new TranslationServiceProvider());
$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
            $translator->addLoader('yaml', new YamlFileLoader());

            $translator->addResource('yaml', __DIR__ . '/../resources/locales/en.yml', 'en');

            return $translator;
        }));

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../log/app.log',
    'monolog.name' => 'app',
    'monolog.level' => 300 // = Logger::WARNING
));

$app->register(new TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true
    ),
    'twig.form.templates' => array('form_div_layout.html.twig', 'common/form_div_layout.html.twig'),
    'twig.path' => array(__DIR__ . '/../resources/views')
));

/*
 * assetic
 */
if (isset($app['assetic.enabled']) && $app['assetic.enabled']) {
    $app->register(new AsseticServiceProvider(), array(
        'assetic.options' => array(
            'debug' => $app['debug'],
            'auto_dump_assets' => $app['debug'],
        )
    ));

    $app['assetic.filter_manager'] = $app->share(
        $app->extend('assetic.filter_manager', function($fm, $app) {
                $fm->set('lessphp', new Assetic\Filter\LessphpFilter());

                return $fm;
            })
    );

    $app['assetic.asset_manager'] = $app->share(
        $app->extend('assetic.asset_manager', function($am, $app) {
                $am->set('styles', new Assetic\Asset\AssetCache(
                    new Assetic\Asset\GlobAsset(
                    $app['assetic.input.path_to_css'], array($app['assetic.filter_manager']->get('lessphp'))
                    ), new Assetic\Cache\FilesystemCache($app['assetic.path_to_cache'])
                ));
                $am->get('styles')->setTargetPath($app['assetic.output.path_to_css']);

                $am->set('scripts', new Assetic\Asset\AssetCache(
                    new Assetic\Asset\GlobAsset($app['assetic.input.path_to_js']), new Assetic\Cache\FilesystemCache($app['assetic.path_to_cache'])
                ));
                $am->get('scripts')->setTargetPath($app['assetic.output.path_to_js']);

                return $am;
            })
    );
}

$app->register(new Silex\Provider\DoctrineServiceProvider());

/*
 * The controller registers itself.
 */
new Metagist\WebController($app);

/**
 * Opauth, overwrites some routes.
 * Remember to enable allow_url_include in the php.ini and set the user_agent
 * user_agent="PHP"
 * @link https://github.com/zendframework/zf2/pull/4331
 */
use SilexOpauth\OpauthExtension;
$app->register(new OpauthExtension());

/**
 * Provides a repo for packages
 */
$app->register(new Metagist\RepoProvider());

return $app;