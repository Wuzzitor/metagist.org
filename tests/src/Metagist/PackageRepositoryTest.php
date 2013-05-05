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
     * connection mock
     * @var \Doctrine\DBAL\Connection 
     */
    private $connection;
    
    /**
     * client mock
     * @var \Packagist\Api\Client
     */
    private $client;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->connection = $this->getMockBuilder("\Doctrine\DBAL\Connection")
            ->disableOriginalConstructor()
            ->getMock();
        $this->client = $this->getMockBuilder("\Packagist\Api\Client")
            ->disableOriginalConstructor()
            ->getMock();
        $this->repo = new PackageRepository($this->connection, $this->client);
    }
    
    /**
     * Ensures the params are validated.
     */
    public function testByAuthorAndNameExceptionIfWrongAuthor()
    {
        $this->setExpectedException("\InvalidArgumentException");
        $this->repo->byAuthorAndName(';;', ';;');
    }
    
    /**
     * Ensures the params are validated.
     */
    public function testByAuthorAndNameExceptionIfWrongName()
    {
        $this->setExpectedException("\InvalidArgumentException");
        $this->repo->byAuthorAndName('test', ';;');
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