<?php
namespace Metagist;

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the api controller.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ApiControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * 
     * @var ApiController
     */
    private $controller;
    
    /**
     * application mock
     * @var Application
     */
    private $application;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->application = $this->getMockBuilder("\Metagist\Application")
            ->disableOriginalConstructor()
            ->getMock();
        $this->application->expects($this->any())
            ->method('match')
            ->will($this->returnValue($this));
        $this->application->expects($this->any())
            ->method('json')
            ->will($this->returnCallback(array($this, 'json')));
        $this->controller = new ApiController($this->application);
    }
    
    /**
     * Ensures the index action returns the routes.
     */
    public function testIndexReturnsRoutes()
    {
        $response = $this->controller->index();
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
        
        $routes = json_decode($response->getContent());
        $this->assertInternalType('array', $routes);
        $this->assertNotEmpty($routes);
    }
    
    /**
     * Ensures package info is returned as json.
     */
    public function testPackage()
    {
        $packageRepo = $this->createPackageRepo('aname', 'apackage');
        $this->application->expects($this->once())
            ->method('packages')
            ->will($this->returnValue($packageRepo));
        $metainfoRepo = $this->createMetaInfoRepo();
        $this->application->expects($this->once())
            ->method('metainfo')
            ->will($this->returnValue($metainfoRepo));
        
        $response = $this->controller->package('aname', 'apackage');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $package = json_decode($response->getContent());
        $this->assertEquals('aname/apackage', $package->identifier);
    }
    
    /**
     * Mocks the controllers bind behaviour
     */
    public function bind($route)
    {
        
    }
    
    /**
     * Mocks the json() behaviour of the application.
     * 
     * @param type $data
     * @param type $status
     * @param type $headers
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function json($data = array(), $status = 200, $headers = array())
    {
        return new \Symfony\Component\HttpFoundation\JsonResponse($data, $status, $headers);
    }
    
    /**
     * Creates a package repo mock.
     * 
     */
    protected function createPackageRepo($author, $name)
    {
        $packageRepo = $this->getMockBuilder("\Metagist\PackageRepository")
            ->disableOriginalConstructor()
            ->getMock();
        $packageRepo->expects($this->once())
            ->method('byAuthorAndName')
            ->with($author, $name)
            ->will($this->returnValue(new Package($author . "/" . $name)));
        
        return $packageRepo;
    }
    
    /**
     * Creates a metainfo repo mock.
     * 
     */
    protected function createMetaInfoRepo(array $data = array())
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection($data);
        $repo = $this->getMockBuilder("\Metagist\MetaInfoRepository")
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('byPackage')
            ->will($this->returnValue($collection));
        
        return $repo;
    }
}