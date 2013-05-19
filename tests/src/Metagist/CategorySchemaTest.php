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
    
    /**
     * Ensures the category access is returned.
     */
    public function testGetAccessForCategory()
    {
        $role = $this->schema->getAccess('test');
        $this->assertEquals('ROLE_ADMIN', $role);
    }
    
    /**
     * Ensures the category is checked.
     */
    public function testGetAccessForCategoryException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->schema->getAccess('nonono');
    }
    
    /**
     * Ensures the group access is returned.
     */
    public function testGetAccessForGroup()
    {
        $role = $this->schema->getAccess('test', 'testBoolean');
        $this->assertEquals('ROLE_SYSTEM', $role);
    }
    
    /**
     * Ensures the group is checked.
     */
    public function testGetAccessForGroupException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->schema->getAccess('test', 'nonono');
    }
}