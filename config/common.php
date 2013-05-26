<?php

// Locale
$app['locale'] = 'en';
$app['session.default_locale'] = $app['locale'];
$app['translator.messages'] = array(
    'en' => __DIR__.'/../resources/locales/en.yml',
);

// Cache
$app['cache.path'] = __DIR__ . '/../resources/cache';

// Http cache
$app['http_cache.enabled'] = false;
$app['http_cache.cache_dir'] = $app['cache.path'] . '/http';

// Twig cache
$app['twig.options.cache'] = $app['cache.path'] . '/twig';

// Assetic
$app['assetic.enabled']              = true;
$app['assetic.path_to_cache']        = $app['cache.path'] . '/assetic' ;
$app['assetic.path_to_web']          = __DIR__ . '/../web/assets';
$app['assetic.input.path_to_assets'] = __DIR__ . '/../resources/assets';

$app['assetic.input.path_to_css']       = $app['assetic.input.path_to_assets'] . '/less/style.less';
$app['assetic.output.path_to_css']      = 'css/styles.css';
$app['assetic.input.path_to_js']        = array(
    __DIR__.'/../../vendor/twitter/bootstrap/js/*.js',
    $app['assetic.input.path_to_assets'] . '/js/script.js',
);
$app['assetic.output.path_to_js']       = 'js/scripts.js';

/*
 * category group icons
 */
$app['category.icons.mapping'] = array(
    'documentation_api' => 'icon-sitemap',
    'documentation_manual' => 'icon-book',
    'transparency_repository' => 'icon-github',
);

/**
 * Configuration how to render category group entries
 */
$app['category.render.mapping'] = array(
    'reliability/usage' => array('class' => 'label label-info'),
    'documentation/api' => array('displayAs' => 'url'),
    'transparency/repository' => array('displayAs' => 'url'),
    'testing/tests.status' => array('displayAs' => 'badge'),
);