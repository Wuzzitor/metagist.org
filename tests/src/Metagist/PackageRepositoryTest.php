<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the package repo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var PackageRepository
     */
    private $repo;
    
    /**
     * connectio mock
     * @var \Doctrine\DBAL\Connection 
     */
    private $connection;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->connection = $this->getMockBuilder("\Doctrine\DBAL\Connection")
            ->disableOriginalConstructor()
            ->getMock();
        $this->repo = new PackageRepository($this->connection);
    }
    
    /**
     * Ensure the name validator works.
     * 
     * @dataProvider nameProvider
     * @param string $name
     * @param bool   $valid
     */
    public function testIsValidName($name, $valid)
    {
        $this->assertEquals($valid, $this->repo->isValidName($name));
    }
    
    public function nameProvider()
    {
        return array(
            array('test', true),
            array('test123', true),
            array('test-123', true),
            array('test-123-TEST', true),
            array('test-12.3', true),
            array('t', false),
            array('1', false),
            array('test/123', false),
            array('test;123', false),
        );
    }
}