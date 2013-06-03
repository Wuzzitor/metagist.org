<?php
namespace Metagist;

/**
 * Api Controller.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ApiController extends Controller
{
    /**
     * routes.
     * @var array 
     */
    protected $routes = array(
        'api-homepage'      => array('match' => '/api', 'method' => 'index'),
        'api-package'       => array('match' => '/api/package/{author}/{name}', 'method' => 'package'),
    );
    
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
        $schema    = $this->application->categories();
        $data = array(
            'identifier'  => $package->getIdentifier(),
            'description' => $package->getDescription(),
            'timeUpdated' => $package->getTimeUpdated(),
            'versions'    => $package->getVersions(),
        );
        
        $infos = array();
        foreach ($schema->getCategories() as $categoryName => $nil) {
            $infos[$categoryName] = array();
            $metainfos = $package->getMetaInfos($categoryName);
            foreach ($metainfos as $metainfo) {
                $infos[$categoryName][] = array($metainfo->getGroup() => $metainfo->getValue());
            }
        }
        $data['metaInfo'] = $infos;
        
        return $this->application->json($data);
    }
}