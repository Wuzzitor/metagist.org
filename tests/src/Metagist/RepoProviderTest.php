<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the service provider
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class RepoProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var \Metagist\RepoProvider;
     */
    private $provider;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->provider = new RepoProvider();
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
        
        $this->assertNotNull($app[RepoProvider::CATEGORY_SCHEMA]);
        $this->assertInstanceOf("\Metagist\CategorySchema", $app[RepoProvider::CATEGORY_SCHEMA]);
        
        $this->assertNotNull($app[RepoProvider::METAINFO_REPO]);
        $this->assertInstanceOf("\Metagist\MetaInfoRepository", $app[RepoProvider::METAINFO_REPO]);
        
        $this->assertNotNull($app[RepoProvider::PACKAGE_FACTORY]);
        $this->assertInstanceOf("\Metagist\PackageFactory", $app[RepoProvider::PACKAGE_FACTORY]);
        
        $this->assertNotNull($app[RepoProvider::PACKAGE_REPO]);
        $this->assertInstanceOf("\Metagist\PackageRepository", $app[RepoProvider::PACKAGE_REPO]);
    }
}