<?php
namespace Metagist;

use Guzzle\Http\Message\EntityEnclosingRequestInterface;

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
            $this->application->getApi()->validateRequest($request);
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
        
        $this->application->logger()->info('Received signed and schema-valid pushInfo request.');
        
        //check package
        $package = $this->application->packages()->byAuthorAndName($author, $name);
        if ($package == null) {
            $message = 'Unknown package ' . $author . '/' . $name;
            $this->application->logger()->warning($message);
            return $this->application->json($message, 404);
        }
        
        $metaInfo = $this->extractMetaInfoFromRequest($request);
        if ($metaInfo === null) {
            return $this->application->json('parsing error', 500);
        }
        $metaInfo->setPackage($package);
        
        try {
            $this->application->metainfo()->save($metaInfo, 1);
        } catch (\Symfony\Component\Security\Core\Exception\AccessDeniedException $exception) {
            $this->application->logger()->warning('PushInfo: ' . $exception->getMessage());
            return $this->application->json($exception->getMessage(), 403);
        }
        
        return $this->application->json(
            'Received info on ' . $metaInfo->getGroup() . ' for package ' . $package->getIdentifier()
        );
    }
    
    /**
     * Parses the body payload.
     * 
     * @param \Guzzle\Http\Message\EntityEnclosingRequestInterface $request
     * @return null
     */
    protected function extractMetaInfoFromRequest(EntityEnclosingRequestInterface $request)
    {
        $json = $request->getBody()->__toString();
        $data = json_decode($json, true);
        return MetaInfo::fromArray($data['info']);
    }
}