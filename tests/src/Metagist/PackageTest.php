<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the package class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var Package 
     */
    private $package;
    
    public function setUp()
    {
        parent::setUp();
        $this->package = new Package('test/123');
    }
    
    /**
     * Ensures the constructor assigns the identifier.
     */
    public function testAssertConstructorWorks()
    {
        $this->assertEquals('test/123', $this->package->getIdentifier());
    }
    
    /**
     * Ensures the author part can be extracted.
     */
    public function testGetAuthor()
    {
        $this->assertEquals('test', $this->package->getAuthor());
    }
    
    /**
     * Ensures the name part can be extracted.
     */
    public function testGetName()
    {
        $this->assertEquals('123', $this->package->getName());
    }
    
    /**
     * Ensures the name part extraction fails with wrong identifier
     */
    public function testGetNameFails()
    {
        $this->package = new Package('test');
        $this->assertFalse($this->package->getName());
    }
    
    /**
     * Ensures description getter / setter are working
     */
    public function testDescription()
    {
        $this->package->setDescription('test');
        $this->assertEquals('test', $this->package->getDescription());
    }
    
    /**
     * Ensures versions getter / setter are working
     */
    public function testVersions()
    {
        $this->package->setVersions(array('test', '1.0.1'));
        $this->assertEquals(array('test', '1.0.1'), $this->package->getVersions());
    }
    
    /**
     * Ensures versions getter / setter are working
     */
    public function testGetTimeUpdated()
    {
        $time = '2013-10-31 00:30:00';
        $this->package->setTimeUpdated($time);
        $this->assertEquals($time, $this->package->getTimeUpdated());
    }
    
    /**
     * Ensures id getter is working
     */
    public function testGetId()
    {
        $this->package = new Package('id/test', 10);
        $this->assertEquals(10, $this->package->getId());
        
        $this->package->setId(123);
        $this->assertEquals(123, $this->package->getId());
    }
    
    /**
     * Ensures a collection of metainfos can be passed.
     */
    public function testCollections()
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection(
            array(MetaInfo::fromValue('test/test', 'test'))
        );
        $this->package->setMetaInfos($collection);
        $this->assertEquals($collection, $this->package->getMetaInfos());
    }
    
    /**
     * Ensures type getter / setter are working
     */
    public function testType()
    {
        $this->package->setType('library');
        $this->assertEquals('library', $this->package->getType());
    }
    
    /**
     * Ensures the collection is filtered by category
     */
    public function testGetMetaInfosWithGroupFilter()
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection(
            array(
                MetaInfo::fromValue('test', 'test'),
                MetaInfo::fromValue('test1', 'test'),
                MetaInfo::fromValue('test', 'test'),
            )
        );
        $this->package->setMetaInfos($collection);
        
        $filtered = $this->package->getMetaInfos('test');
        $this->assertInstanceOf("\Doctrine\Common\Collections\ArrayCollection", $filtered);
        $this->assertEquals(2, count($filtered));
    }
    
    /**
     * Ensures the toString method returns the name without author/owner
     */
    public function testToStringReturnsOnlyTheName()
    {
        $this->assertEquals('123', $this->package->__toString());
    }
}