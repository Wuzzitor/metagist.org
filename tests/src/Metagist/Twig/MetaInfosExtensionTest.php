<?php
namespace Metagist\Twig;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the twig extension to render metainfo collections (of the same group).
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfosExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MetaInfosExtension
     */
    private $extension;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->extension = new MetaInfosExtension(
            array('test/list' => MetaInfosExtension::STRATEGY_UNMODIFIED)
        );
    }
    
    /**
     * Test the usable methods.
     */
    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertNotEmpty($functions);
        $this->assertArrayHasKey('renderInfos', $functions);
    }
    
    /**
     * Ensures a collection is rendered even if the strategy is not defined.
     */
    public function testRenderInfosAsListIsDefault() 
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection();
        $collection->add(\Metagist\MetaInfo::fromValue('test/unknown', 'http://an.url'));
        $collection->add(\Metagist\MetaInfo::fromValue('test/unknown', 'http://an.other.url'));
        
        $list = $this->extension->renderInfos($collection);
        $this->assertContains('<li>http://an.url</li>', $list);
        $this->assertContains('<li>http://an.other.url</li>', $list);
    }
    
    /**
     * Ensures a collection is rendered as list if configured so.
     */
    public function testRenderInfosAsList() 
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection();
        $collection->add(\Metagist\MetaInfo::fromValue('test/list', 'http://an.url'));
        $collection->add(\Metagist\MetaInfo::fromValue('test/list', 'http://an.other.url'));
        
        $list = $this->extension->renderInfos($collection);
        $this->assertContains('<li>http://an.url</li>', $list);
        $this->assertContains('<li>http://an.other.url</li>', $list);
    }
    
    
    /**
     * Test the name of the extension.
     */
    public function testGetName()
    {
        $this->assertEquals(MetaInfosExtension::NAME, $this->extension->getName());
    }
}
