<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the service provider
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var \Metagist\ServiceProvider;
     */
    private $provider;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->provider = new ServiceProvider();
    }
    
    /**
     * Ensures \Silex\ServiceProviderInterface is implemented.
     */
    public function testImplementsInterface()
    {
        $this->assertInstanceOf("\Silex\ServiceProviderInterface", $this->provider);
    }
    
    /**
     * Ensures the three services are registered.
     */
    public function testRegistersServices()
    {
        $app = new \Silex\Application();
        $app['db'] = $this->getMockBuilder("\Doctrine\DBAL\Connection")
            ->disableOriginalConstructor()
            ->getMock();
        $this->provider->register($app);
        
        $this->assertNotNull($app[ServiceProvider::CATEGORY_SCHEMA]);
        $this->assertInstanceOf("\Metagist\CategorySchema", $app[ServiceProvider::CATEGORY_SCHEMA]);
        
        $this->assertNotNull($app[ServiceProvider::METAINFO_REPO]);
        $this->assertInstanceOf("\Metagist\MetaInfoRepository", $app[ServiceProvider::METAINFO_REPO]);
        
        $this->assertNotNull($app[ServiceProvider::METAINFO_FACTORY]);
        $this->assertInstanceOf("\Metagist\MetaInfoFactory", $app[ServiceProvider::METAINFO_FACTORY]);
        
        $this->assertNotNull($app[ServiceProvider::PACKAGE_FACTORY]);
        $this->assertInstanceOf("\Metagist\PackageFactory", $app[ServiceProvider::PACKAGE_FACTORY]);
        
        $this->assertNotNull($app[ServiceProvider::PACKAGE_REPO]);
        $this->assertInstanceOf("\Metagist\PackageRepository", $app[ServiceProvider::PACKAGE_REPO]);
        
        $this->assertNotNull($app[ServiceProvider::RATINGS_REPO]);
        $this->assertInstanceOf("\Metagist\RatingRepository", $app[ServiceProvider::RATINGS_REPO]);
    }
}