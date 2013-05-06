<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the metainfo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MetaInfo 
     */
    private $metaInfo;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->metaInfo = new MetaInfo();
    }
    
    /**
     * Ensures the constructor assigns the identifier.
     */
    public function testFactoryMethod()
    {
        $info = MetaInfo::fromArray(array());
        $this->assertInstanceOf('Metagist\MetaInfo', $info);
    }
    
    /**
     * Tests the category getter.
     */
    public function testGetCategory()
    {
        $this->metaInfo = MetaInfo::fromArray(array('category' => 'test'));
        $this->assertEquals('test', $this->metaInfo->getCategory());
    }
    
    /**
     * Tests the group getter.
     */
    public function testGetGroup()
    {
        $this->metaInfo = MetaInfo::fromArray(array('group' => 'test'));
        $this->assertEquals('test', $this->metaInfo->getGroup());
    }
    
    /**
     * Tests the value getter.
     */
    public function testGetValue()
    {
        $this->metaInfo = MetaInfo::fromArray(array('value' => 'test'));
        $this->assertEquals('test', $this->metaInfo->getValue());
    }
}