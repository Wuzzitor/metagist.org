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
     * Validator 
     * @var Validator
     */
    private $validator;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->connection = $this->getMockBuilder("\Doctrine\DBAL\Connection")
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = new Validator(
            new CategorySchema(file_get_contents(__DIR__ . '/testdata/testcategories.json'))
        );
        $this->repo = new PackageRepository($this->connection, $this->validator);
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
     * Ensures null is returned if the package has not been found.
     */
    public function testPackageNotFoundReturnsNull()
    {
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(false));
        
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        
        $this->repo->byAuthorAndName('test', 'test');
    }
    
    /**
     * Ensures a package is returned if found.
     */
    public function testPackageIsFound()
    {
        $data = array(
            'id' => 1,
            'identifier' => 'test/test',
            'description' => 'test',
            'versions' => 'dev-master',
        );
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($data));
        
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        
        $this->repo->byAuthorAndName('test', 'test');
    }
    
    /**
     * Ensures a package with an id is updated.
     */
    public function testSaveWithId()
    {
        $package = new Package('test/test', 123);
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));
        
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->with($this->stringContains('UPDATE packages'))
            ->will($this->returnValue($statement));
        
        $this->repo->save($package);
    }
    
    /**
     * Ensures a package without an id is inserted.
     */
    public function testSaveWithoutId()
    {
        $package = new Package('test/test');
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));
        $statement->expects($this->once())
            ->method('lastInsertId')
            ->will($this->returnValue(123));
        
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->with($this->stringContains('INSERT INTO packages'))
            ->will($this->returnValue($statement));
        
        $this->repo->save($package);
        $this->assertEquals(123, $package->getId());
    }
    
    /**
     * Creates a statement mock, the provided HydratorMockStatement seems to be broken.
     * 
     * @param array $methods
     * @return Statement mock
     */
    protected function createMockStatement(array $methods = array('rowCount', 'fetch', 'lastInsertId'))
    {
        return $this->getMock('stdClass', $methods);
    }
}