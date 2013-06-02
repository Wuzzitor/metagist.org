<?php
namespace Metagist;

/**
 * Abstract controller class, provides shared methods.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
abstract class Controller
{
    /**
     * the application instance
     * @var Application 
     */
    protected $application;

    /**
     * Constructor.
     * 
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->application = $app;
        $this->initRoutes();
    }
    
    /**
     * Routing setup.
     * 
     * 
     */
    abstract protected function initRoutes();
    
    /**
     * Retrieves a package either from the db or packagist.
     * 
     * @param string $author
     * @param string $name
     * @return Package
     */
    protected function getPackage($author, $name)
    {
        $packageRepo = $this->application->packages();
        $package = $packageRepo->byAuthorAndName($author, $name);
        if ($package == null) {
            $package = $this->application[ServiceProvider::PACKAGE_FACTORY]->byAuthorAndName($author, $name);
            if ($packageRepo->save($package)) {
                /* @var $metaInfoRepo MetaInfoRepository */
                $metaInfoRepo = $this->application[ServiceProvider::METAINFO_REPO];
                $metaInfoRepo->savePackage($package);
            }
        }

        return $package;
    }
    
    /**
     * Returns the current user.
     * 
     * @return \Metagist\User|null
     */
    protected function getUser()
    {
        $token = $this->application->security()->getToken();
        return (null !== $token) ? $token->getUser() : null;
    }
}