<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the metainfo repo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MetaInfoFactory
     */
    private $factory;
    
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->factory = new MetaInfoFactory();
    }
    
    /**
     * Ensures a collection of metainfos is returned.
     */
    public function testFromPackagistPackage()
    {
        $package = $this->getMockBuilder("\Packagist\Api\Result\Package")
            ->disableOriginalConstructor()
            ->getMock();
        
        $version = $this->getMock("\Packagist\Api\Result\Package\Version");
        $version->expects($this->once())
            ->method('getLicense')
            ->will($this->returnValue(array('test')));
        $versions = array(
            $version
        );
        $package->expects($this->once())
            ->method('getVersions')
            ->will($this->returnValue($versions));
        
        $collection = $this->factory->fromPackagistPackage($package);
        $this->assertInstanceOf("\Doctrine\Common\Collections\Collection", $collection);
        $this->assertEquals(6, count($collection));
        $this->assertInstanceOf("\Metagist\MetaInfo", $collection->first());
    }
    
    public function testFromPackagistPackageHasNoVersionReturnsArray()
    {
        $package = $this->getMockBuilder("\Packagist\Api\Result\Package")
            ->disableOriginalConstructor()
            ->getMock();
        $package->expects($this->once())
            ->method('getVersions')
            ->will($this->returnValue(array()));
        $collection = $this->factory->fromPackagistPackage($package);
        $this->assertEmpty($collection);
    }
    
    /**
     * Ensures the github client can be injected.
     */
    public function testInjectGithubClient()
    {
       $client = $this->getMockBuilder("\Github\Client")
           ->disableOriginalConstructor()
           ->getMock();
       $this->factory->setGitHubClient($client);
       $this->assertAttributeEquals($client, 'githubClient', $this->factory);
    }
    
    /**
     * Ensures that the client must be injected first.
     */
    public function testFromGithubRepoThrowsNoClientException()
    {
        $this->setExpectedException("\RuntimeException");
        $this->factory->fromGithubRepo('http://an.url');
    }
    
    /**
     * Ensures that only github.com urls are parsed.
     */
    public function testFromGithubRepoReturnsNullIfUrlNotGithub()
    {
        $client = $this->getMockBuilder("\Github\Client")
           ->disableOriginalConstructor()
           ->getMock();
        $this->factory->setGitHubClient($client);
        $result = $this->factory->fromGithubRepo('http://an.url');
        $this->assertNull($result);
    }
    
    /**
     * Ensures that only urls with path are parsed.
     */
    public function testFromGithubRepoReturnsNullIfNoPath()
    {
        $client = $this->getMockBuilder("\Github\Client")
           ->disableOriginalConstructor()
           ->getMock();
        $this->factory->setGitHubClient($client);
        $result = $this->factory->fromGithubRepo("https://github.com/");
        $this->assertNull($result);
    }
    
    /**
     * Ensures that only urls with path are parsed.
     */
    public function testFromGithubRepoCollectsContributorsAndCommits()
    {
        $client = $this->getMockBuilder("\Github\Client")
           ->disableOriginalConstructor()
           ->getMock();
        $response = $this->getMock("\Github\HttpClient\Message\Response");
        $response->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue(array()));
        $httpClient = $this->getMock("\Github\HttpClient\HttpClientInterface");
        $httpClient->expects($this->any())
            ->method('get')
            ->will($this->returnValue($response));
        $client->expects($this->any())
            ->method('getHttpClient')
            ->will($this->returnValue($httpClient));
        
        $this->factory->setGitHubClient($client);
        $result = $this->factory->fromGithubRepo("https://github.com/owner/repo");
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $result);
    }
}