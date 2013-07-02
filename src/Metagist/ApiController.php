<?php
namespace Metagist;

/**
 * Api Controller.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ApiController extends Controller implements \Metagist\Api\ServerInterface
{
    /**
     * routes.
     * @var array 
     */
    protected $routes = array(
        'api-homepage'      => array('match' => '/api', 'method' => 'index'),
        'api-package'       => array('match' => '/api/package/{author}/{name}', 'method' => 'package'),
        'api-pushInfo'      => array('match' => '/api/pushInfo/{author}/{name}', 'method' => 'pushInfo'),
    );
    
    /**
     * incoming request
     * 
     * @var \Symfony\Component\HttpFoundation\Request|null 
     */
    protected $request;
    
    /**
     * Setup of the api routes.
     * 
     * 
     */
    protected function initRoutes()
    {
        foreach ($this->routes as $name => $data) {
            $this->application
                ->match($data['match'], array($this, $data['method']))
                ->bind($name);
        }
    }

    /**
     * Index: returns the available routes
     * 
     * @return string
     */
    public function index()
    {
        $calls = array();
        foreach ($this->routes as $route) {
            $calls[] = $route['match'];
        }
        return $this->application->json($calls);
    }
    
    /**
     * Returns the package content as json.
     * 
     * @param string $author
     * @param string $name
     * @return string
     */
    public function package($author, $name)
    {
        $package   = $this->getPackage($author, $name);
        $package->setMetaInfos(
            $this->application->metainfo()->byPackage($package)
        );
        
        $serializer = $this->application->getApi()->getSerializer();
        $body = $serializer->serialize($package, 'json');
        
        $response = \Symfony\Component\HttpFoundation\Response::create(
            $body, 200, array('application/json')
        );
        return $response;
    }
    
    /**
     * Receive metainfo updates from a worker.
     * 
     * @param string $author
     * @param string $name
     * @param \Metagist\MetaInfo $info
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function pushInfo($author, $name, MetaInfo $info = null)
    {
        $request = $this->application->getApi()->getIncomingRequest();
        /* @var $request \Guzzle\Http\Message\EntityEnclosingRequest */
        
        //validate oauth
        try {
            $consumerKey = $this->application->getApi()->validateRequest($request);
            $this->application->getOpauthListener()->onWorkerAuthentication($consumerKey);
        } catch (\Metagist\Api\Exception $exception) {
            $this->application->logger()->warning('Error authorizing a pushInfo request: ' . $exception->getMessage());
            return $this->application->json('Authorization failed: ' . $exception->getMessage(), 403);
        }
        
        //validate json integrity
        try {
            $validator = $this->application->getApi()->getSchemaValidator();
            $validator->validateRequest($request, 'pushInfo');
        } catch (\Metagist\Api\Validation\Exception $exception) {
            $this->application->logger()->warning('Error validating a pushInfo request: ' . $exception->getMessage());
            return $this->application->json('Invalid content: ' . $exception->getMessage(), 400);
        }
        
        $this->application->logger()->info('Received pushInfo from worker ' . $consumerKey);
        
        //check package
        $package = $this->application->packages()->byAuthorAndName($author, $name);
        if ($package == null) {
            $message = 'Unknown package ' . $author . '/' . $name;
            $this->application->logger()->warning($message);
            return $this->application->json($message, 404);
        }
        
        $serializer = $this->application->getApi()->getSerializer();
        try {
            $metaInfo   = $serializer->deserialize($request->getBody()->__toString(), "Metagist\MetaInfo", 'json');
        } catch (\JMS\Parser\SyntaxErrorException $exception) {
            $this->application->logger()->error($exception->getMessage() . ': ' . $request->getBody()->__toString());
            $this->application->logger()->error('Request: ' . $request->__toString());
            return $this->application->json('parsing error', 500);
        }
        $metaInfo->setPackage($package);
        
        $this->application->metainfo()->save($metaInfo, 1);
        
        return $this->application->json(
            'Received info on ' . $metaInfo->getGroup() . ' for package ' . $package->getIdentifier()
        );
    }
}