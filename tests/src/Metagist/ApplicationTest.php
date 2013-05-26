<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the metagist application
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var Application
     */
    private $app;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->app = new \Metagist\Application();
    }
    
    /**
     * Session shortcut test
     */
    public function testProvidesSessionShortcut()
    {
        $test = new \stdClass();
        $this->app['session'] = function () use ($test) {
            return $test;
        };
        
        $this->assertSame($test, $this->app->session());
    }
    
    /**
     * monolog shortcut test
     */
    public function testProvidesMonologShortcut()
    {
        $test = new \stdClass();
        $this->app['monolog'] = function () use ($test) {
            return $test;
        };
        
        $this->assertSame($test, $this->app->logger());
    }
    
    /**
     * packge repo shortcut test
     */
    public function testProvidesPackageRepoShortcut()
    {
        $test = new \stdClass();
        $this->app[ServiceProvider::PACKAGE_REPO] = function () use ($test) {
            return $test;
        };
        
        $this->assertSame($test, $this->app->packages());
    }
    
    /**
     * metainfo repo shortcut test
     */
    public function testMetaInfoRepoShortcutReturnsProxy()
    {
        $this->app['security'] = function () {
            return $this->getMockBuilder("\Symfony\Component\Security\Core\SecurityContextInterface")
                ->disableOriginalConstructor()
                ->getMock();
        };
        
        $this->app['categories'] = function () {
            return $this->getMockBuilder("\Metagist\CategorySchema")
                ->disableOriginalConstructor()
                ->getMock();
        };
        
        $this->app[ServiceProvider::METAINFO_REPO] = function () {
            return $this->getMockBuilder("\Metagist\MetaInfoRepository")
                ->disableOriginalConstructor()
                ->getMock();
        };
        
        $this->assertInstanceOf("\Metagist\MetaInfoRepositoryProxy", $this->app->metainfo());
    }
    
    /**
     * rating repo shortcut test
     */
    public function testProvidesRatingRepoShortcut()
    {
        $test = new \stdClass();
        $this->app[ServiceProvider::RATINGS_REPO] = function () use ($test) {
            return $test;
        };
        
        $this->assertSame($test, $this->app->ratings());
    }
    
    /**
     * schema shortcut test
     */
    public function testProvidesCategorySchemaShortcut()
    {
        $test = new \stdClass();
        $this->app[ServiceProvider::CATEGORY_SCHEMA] = function () use ($test) {
            return $test;
        };
        
        $this->assertSame($test, $this->app->categories());
    }
    
    /**
     * security shortcut test
     */
    public function testProvidesSecurityShortcut()
    {
        $test = new \stdClass();
        $this->app['security'] = function () use ($test) {
            return $test;
        };
        
        $this->assertSame($test, $this->app->security());
    }
    
    /**
     * twig render shortcut test
     */
    public function testProvidesRenderShortcut()
    {
        $test = $this->getMock("\stdClass", array('render', 'addExtension'));
        $test->expects($this->once())
            ->method('render')
            ->with('template', array());
        
        $this->app['twig'] = function () use ($test) {
            return $test;
        };
        $this->app['category.icons.mapping'] = array();
        $this->app['category.render.mapping'] = array();
        
        $this->app->render('template', array());
    }
    
    /**
     * twig render shortcut test
     */
    public function testRunUsesHttpCache()
    {
        $test = $this->getMock("\stdClass", array('run'));
        $test->expects($this->once())
            ->method('run');
        
        $this->app['http_cache'] = function () use ($test) {
            return $test;
        };
        
        $this->app->run();
    }
}