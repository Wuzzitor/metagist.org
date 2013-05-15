<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the category schema class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class CategorySchemaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var CategorySchema
     */
    private $schema;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
    
        $json = file_get_contents(__DIR__ .'/testdata/testcategories.json');
        $this->schema = new CategorySchema($json);
    }
    
    /**
     * Ensures the constructor checks the json.
     */
    public function testConstructorThrowsException()
    {
       $this->setExpectedException('\InvalidArgumentException'); 
       $this->schema = new CategorySchema('');
    }
    
    /**
     * Ensures categories are returned.
     */
    public function testGetCategories()
    {
        $cats = $this->schema->getCategories();
        $this->assertNotNull($cats);
    }
    
    /**
     * Ensures groups are returned.
     */
    public function testGetGroups()
    {
        $groups = $this->schema->getGroups('test');
        $this->assertNotNull($groups);
        $this->assertInternalType('array', $groups);
    }
}