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
     * Ensures id getter is working
     */
    public function testGetId()
    {
        $this->package = new Package('id/test', 10);
        $this->assertEquals(10, $this->package->getId());
    }
}