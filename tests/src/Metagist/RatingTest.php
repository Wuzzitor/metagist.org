<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the rating model
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class RatingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var Rating 
     */
    private $rating;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->rating = new Rating();
    }
    
    /**
     * Ensures the array factory method returns a rating object.
     */
    public function testFactoryMethod()
    {
        $rating = Rating::fromArray(array());
        $this->assertInstanceOf('Metagist\Rating', $rating);
    }
    
    /**
     * Ensures the value factory method returns a metainfo object.
     */
    public function testFactoryMethodStoresValues()
    {
        $package = new Package('aaa/bbb', 12);
        $rating = Rating::fromArray(
            array(
                'package' => $package,
                'user_id' => 12,
                'rating' => 3,
                'comment' => 'test comment',
                'version' => '1.0.0',
                'time_updated' => '2012-12-12 00:00:00'
            )
        );
        $this->assertEquals($package, $rating->getPackage());
        $this->assertEquals(3, $rating->getRating());
        $this->assertEquals(12, $rating->getUserId());
        $this->assertEquals('test comment', $rating->getComment());
        $this->assertEquals('1.0.0', $rating->getVersion());
        $this->assertEquals('2012-12-12 00:00:00', $rating->getTimeUpdated());
    }
    
}