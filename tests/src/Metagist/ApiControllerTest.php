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
            ->method('logger')
            ->will($this->returnValue($this->getMock("\Psr\Log\LoggerInterface")));
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
        $api = $this->createMockApi();
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $api->expects($this->any())
            ->method('getSerializer')
            ->will($this->returnValue($serializer));
        
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
     * Tests the successful execution of pushInfo().
     */
    public function testPushInfo()
    {
        $api = $this->createMockApi();
        $api->expects($this->once())
            ->method('validateRequest')
            ->will($this->returnValue('aconsumer'));
        $serializerMock = $this->getMock("\JMS\Serializer\SerializerInterface");
        $api->expects($this->any())
            ->method('getSerializer')
            ->will($this->returnValue($serializerMock));
        
        $validatorMock = $this->getMockBuilder("\Metagist\Api\Validation\Plugin\SchemaValidator")
            ->disableOriginalConstructor()
            ->getMock();
        $api->expects($this->once())
            ->method('getSchemaValidator')
            ->will($this->returnValue($validatorMock));
        $api->expects($this->once())
            ->method('getIncomingRequest')
            ->will($this->returnValue($this->createPushInfoRequest()));
        
        $this->createOpauthListenerMock();
        
        //package is found
        $packageRepo = $this->createPackageRepo('aname', 'apackage');
        $this->application->expects($this->once())
            ->method('packages')
            ->will($this->returnValue($packageRepo));
        
        //decode payload
        $data = array(
            'group' => 'test', 
            'value' => 'a test value',
            'version' => '0.0.1'
        );
        $serializerMock->expects($this->once())
            ->method('deserialize')
            ->will($this->returnValue(MetaInfo::fromValue('test', 'a value', '1.0.2')));
        $metaInfoRepo = $this->createMetaInfoRepo();
        $metaInfoRepo->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf("\Metagist\MetaInfo"));
        $this->application->expects($this->once())
            ->method('metainfo')
            ->will($this->returnValue($metaInfoRepo));
        
        
        $response = $this->controller->pushInfo('aname', 'apackage');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
    }
    
    /**
     * Ensures the request is denied with proper authorization
     */
    public function testPushInfoFailsForWrongAuthorization()
    {
        $api = $this->createMockApi();
        $api->expects($this->once())
            ->method('validateRequest')
            ->will($this->throwException(new \Metagist\Api\Exception('test')));
        $api->expects($this->once())
            ->method('getIncomingRequest')
            ->will($this->returnValue($this->createPushInfoRequest()));
        
        $repo = $this->createMetaInfoRepo();
        $repo->expects($this->never())
            ->method('save');
        $this->createOpauthListenerMock();
        
        
        $response = $this->controller->pushInfo('author', 'name');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(403, $response->getStatusCode());
    }
    
    /**
     * Ensures a 404 is sent if the package cannot be found.
     */
    public function testPushInfoFailsForinvalidJson()
    {
        $api = $this->createMockApi();
        $api->expects($this->once())
            ->method('validateRequest')
            ->will($this->returnValue('aconsumer'));
        $serializerMock = $this->getMock("\JMS\Serializer\SerializerInterface");
        $api->expects($this->any())
            ->method('getSerializer')
            ->will($this->returnValue($serializerMock));
        $this->createOpauthListenerMock();
        
        $validatorMock = $this->getMockBuilder("\Metagist\Api\Validation\Plugin\SchemaValidator")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock->expects($this->once())
            ->method('validateRequest')
            ->will($this->throwException(new \Metagist\Api\Validation\Exception('test', 400)));
        $api->expects($this->once())
            ->method('getSchemaValidator')
            ->will($this->returnValue($validatorMock));
        $api->expects($this->once())
            ->method('getIncomingRequest')
            ->will($this->returnValue($this->createPushInfoRequest()));
        
        $response = $this->controller->pushInfo('aname', 'apackage');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(400, $response->getStatusCode());
    }
    
    /**
     * Ensures a 404 is sent if the package cannot be found.
     */
    public function testPushInfoFailsForMissingPackage()
    {
        
        $api = $this->createMockApi();
        $api->expects($this->once())
            ->method('validateRequest')
            ->will($this->returnValue('aconsumer'));
        $serializerMock = $this->getMock("\JMS\Serializer\SerializerInterface");
        $api->expects($this->any())
            ->method('getSerializer')
            ->will($this->returnValue($serializerMock));
        $this->createOpauthListenerMock();
        
        $validatorMock = $this->getMockBuilder("\Metagist\Api\Validation\Plugin\SchemaValidator")
            ->disableOriginalConstructor()
            ->getMock();
        $api->expects($this->once())
            ->method('getSchemaValidator')
            ->will($this->returnValue($validatorMock));
        $api->expects($this->once())
            ->method('getIncomingRequest')
            ->will($this->returnValue($this->createPushInfoRequest()));
        
        //package is found
        $packageRepo = $this->createPackageRepo('aname', 'apackage', true);
        $this->application->expects($this->once())
            ->method('packages')
            ->will($this->returnValue($packageRepo));
        
        $response = $this->controller->pushInfo('aname', 'apackage');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
    
    /**
     * 
     */
    protected function createOpauthListenerMock()
    {
        $listenerMock = $this->getMockBuilder("\Metagist\OpauthListener")
            ->disableOriginalConstructor()
            ->getMock();
        $listenerMock->expects($this->any())
            ->method('onWorkerAuthentication')
            ->with('aconsumer');
        $this->application->expects($this->any())
            ->method('getOpauthListener')
            ->will($this->returnValue($listenerMock));
    }
    /**
     * Creates a mocked api.
     * 
     * @return \Metagist\Api\ApiProviderInterface mock
     */
    protected function createMockApi()
    {
        $apiMock = $this->getMock("\Metagist\Api\ApiProviderInterface");
        $this->application->expects($this->any())
            ->method('getApi')
            ->will($this->returnValue($apiMock));
        
        return $apiMock;
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
    protected function createPackageRepo($author, $name, $returnNull = false)
    {
        $packageRepo = $this->getMockBuilder("\Metagist\PackageRepository")
            ->disableOriginalConstructor()
            ->getMock();
        if (!$returnNull) {
            $package = new Package($author . "/" . $name);
        } else {
            $package = null;
        }
        
        $packageRepo->expects($this->once())
            ->method('byAuthorAndName')
            ->with($author, $name)
            ->will($this->returnValue($package));
        
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
        $repo->expects($this->any())
            ->method('byPackage')
            ->will($this->returnValue($collection));
        
        return $repo;
    }
    
    /**
     * Constructs a post request with payload.
     * 
     * @return \Symfony\Component\HttpFoundation\Request
     */
    private function createPushInfoRequest()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            'http://test.com',
            'POST',
            array('author' => 'test', 'name' => 'test'),
            array(),
            array(),
            array(),
            '{"info":{"group":"testInteger","value":12}}'
        );
        
        return $request;
    }
}