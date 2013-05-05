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
        $this->assertAttributeEquals('test/123', 'identifier', $this->package);
    }
}