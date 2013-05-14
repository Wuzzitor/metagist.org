<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the metainfo repo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MetaInfoRepository
     */
    private $repo;
    
    /**
     * connection mock
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
        $this->repo = new MetaInfoRepository($this->connection);
    }
    
    /**
     * Ensures the params are validated.
     */
    public function testByPackage()
    {
        $statement = $this->createMockStatement();
        $statement->expects($this->at(0))
            ->method('fetch')
            ->will($this->returnValue(
                array(
                    'category' => 'cat123',
                    'group' => 'group123',
                    'value' => 'val123'))
            );
        $statement->expects($this->at(1))
            ->method('fetch')
            ->will($this->returnValue(false));
        
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        
        $package = new Package('test/test123', 123);
        $collection = $this->repo->byPackage($package);
        $this->assertInstanceOf("\Doctrine\Common\Collections\Collection", $collection);
        $info = $collection->get(0);
        $this->assertInstanceOf("\Metagist\MetaInfo", $info);
    }
    
    /**
     * Ensures a package can be saved.
     */
    public function testSavePackage()
    {
        $elements = array(MetaInfo::fromValue('test/test', 123));
        $collection = new \Doctrine\Common\Collections\ArrayCollection($elements);
        $package = new Package('test/test123', 123);
        $package->setMetaInfos($collection);
        
        $statement = $this->createMockStatement();
        $statement->expects($this->at(0))
            ->method('rowCount')
            ->will($this->returnValue(1));
        
        $this->connection->expects($this->at(0))
            ->method('executeQuery');
        $this->connection->expects($this->at(1))
            ->method('executeQuery')
            ->with($this->stringContains('INSERT INTO metainfo'))
            ->will($this->returnValue($statement));
        
        $this->repo->savePackage($package);
    }
        
    /**
     * Creates a statement mock, the provided HydratorMockStatement seems to be broken.
     * 
     * @param array $methods
     * @return Statement mock
     */
    protected function createMockStatement(array $methods = array('rowCount', 'fetch'))
    {
        return $this->getMock('stdClass', $methods);
    }
}