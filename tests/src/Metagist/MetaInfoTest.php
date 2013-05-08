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
     * Ensures the array factory method returns a metainfo object.
     */
    public function testFactoryMethod()
    {
        $info = MetaInfo::fromArray(array());
        $this->assertInstanceOf('Metagist\MetaInfo', $info);
    }
    
    /**
     * Ensures the value factory method returns a metainfo object.
     */
    public function testFromValueFactoryMethod()
    {
        $info = MetaInfo::fromValue('cat/grp', 'test123');
        $this->assertInstanceOf('Metagist\MetaInfo', $info);
        $this->assertEquals('cat', $info->getCategory());
        $this->assertEquals('grp', $info->getGroup());
        $this->assertEquals('test123', $info->getValue());
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