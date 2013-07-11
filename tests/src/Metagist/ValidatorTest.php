<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the validator class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var Validator
     */
    private $validator;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $schema = new CategorySchema(file_get_contents(__DIR__ . '/testdata/testcategories.json'));
        $this->validator = new Validator($schema);
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
        $this->assertEquals($valid, $this->validator->isValidName($name));
    }
    
    /**
     * dataprovider
     * @return array
     */
    public function nameProvider()
    {
        return array(
            array('test', true),
            array('test123', true),
            array('test-123', true),
            array('test-123-TEST', true),
            array('test-12.3', false),
            array('t', false),
            array('1', false),
            array('test/123', false),
            array('test;123', false),
        );
    }
    
    /**
     * Ensures the category presence is checked.
     */
    public function testIsValidMetaInfoWithoutCategory()
    {
        $metaInfo = MetaInfo::fromArray(
            array(
                'package' => new Package('test/test'),
                'group' => 'testInteger',
            )
        );
        
        $this->validator->isValidMetaInfo($metaInfo);
    }
    
    /**
     * Ensures the package presence is checked.
     */
    public function testIsValidMetaInfoWithoutPackage()
    {
        $metaInfo = MetaInfo::fromArray(
            array(
                'group' => 'testInteger',
            )
        );
        
        $this->setExpectedException("\Metagist\InvalidInfoException");
        $this->validator->isValidMetaInfo($metaInfo);
    }
    
    /**
     * Ensures the a  type is valid
     * 
     * @dataProvider typeProvider
     */
    public function testTypeIsValid($group, $value, $expected)
    {
        $metaInfo = MetaInfo::fromArray(
            array(
                'category' => 'test',
                'group' => $group,
                'package' => new Package('test/test'),
                'value' => $value
            )
        );
        
        $result = $this->validator->isValidMetaInfo($metaInfo);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * Data provider
     */
    public function typeProvider()
    {
        return array(
            array('testString', 'a string', true),
            array('testString', null, false),
            array('testBoolean', 1, true),
            array('testBoolean', true, true),
            array('testBoolean', "1", true),
            array('testBoolean', "2", false),
            array('testBoolean', "a string", false),
            array('testUrl', "a string", false),
            array('testUrl', "http://metagist.org", true),
            array('testInteger', 12, true),
            array('testInteger', 12.3, false),
            array('testInteger', "a string", false),
            array('testBadge', "http://metagist.org", true),
            array('testBadge', "no-url", false),
        );
    }
    
    /**
     * Ensures isValidCategoryGroup() fails without an existing group
     */
    public function testIsValidCategoryGroupFails()
    {
        $this->assertFalse($this->validator->isValidCategoryGroup('test', 'x'));
    }
    
    /**
     * Ensures isValidCategoryGroup() fails gracefully.
     */
    public function testIsValidCategoryGroupFailsWithException()
    {
        $this->assertFalse($this->validator->isValidCategoryGroup('fail', 'x'));
    }
    
    /**
     * Ensures isValidCategoryGroup() works with an existing group
     */
    public function testIsValidCategoryGroup()
    {
        $this->assertTrue($this->validator->isValidCategoryGroup('test', 'testInteger'));
    }
    
    /**
     * Ensures a valid identifier is regarded as valid
     */
    public function testIsValidIdentifier()
    {
        $identifier = "abcde/fg-hi";
        $this->assertTrue(Validator::isValidIdentifier($identifier));
    }
    
    /**
     * Ensures that no-string args cause an exception.
     */
    public function testIsValidIdentifierNoStringException()
    {
        $this->setExpectedException("\InvalidArgumentException");
        Validator::isValidIdentifier(array());
    }
    
    /**
     * Ensures invalid identifiers are detected.
     * 
     * @param string $invalidIdentifier
     * @dataProvider getInvalidIdentifiers
     */
    public function testIsValidIdentifierFails($invalidIdentifier)
    {
        $this->assertFalse(Validator::isValidIdentifier($invalidIdentifier));
    }
    
    /**
     * Data provider for invalid package identifiers.
     * 
     * @return array
     */
    public function getInvalidIdentifiers()
    {
        return array(
            array('a'),
            array('a/b'),
            array('/b'),
            array('a/'),
            array('has_/underscore'),
            array('is/' . str_repeat('toolong', 30)),
        );
    }
}