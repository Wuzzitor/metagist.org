<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the metainfo repo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MetaInfoFactory
     */
    private $factory;
    
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->factory = new MetaInfoFactory();
    }
    
    /**
     * Ensures a collection of metainfos is returned.
     */
    public function testFromPackagistPackage()
    {
        $package = $this->getMockBuilder("\Packagist\Api\Result\Package")
            ->disableOriginalConstructor()
            ->getMock();
        
        $version = $this->getMock("\Packagist\Api\Result\Package\Version");
        $version->expects($this->once())
            ->method('getLicense')
            ->will($this->returnValue(array('test')));
        $versions = array(
            $version
        );
        $package->expects($this->once())
            ->method('getVersions')
            ->will($this->returnValue($versions));
        
        $collection = $this->factory->fromPackagistPackage($package);
        $this->assertInstanceOf("\Doctrine\Common\Collections\Collection", $collection);
        $this->assertEquals(6, count($collection));
        $this->assertInstanceOf("\Metagist\MetaInfo", $collection->first());
    }
    
    public function testFromPackagistPackageHasNoVersionReturnsArray()
    {
        $package = $this->getMockBuilder("\Packagist\Api\Result\Package")
            ->disableOriginalConstructor()
            ->getMock();
        $package->expects($this->once())
            ->method('getVersions')
            ->will($this->returnValue(array()));
        $collection = $this->factory->fromPackagistPackage($package);
        $this->assertEmpty($collection);
    }
}