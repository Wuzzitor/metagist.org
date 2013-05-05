<?php
namespace Metagist;

/**
 * WebController for Metagist.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class WebController
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
    protected function initRoutes()
    {
        $routes = array(
            'homepage' => array('match' => '/', 'method' => 'index'),
            'errors' => array('match' => '/errors', 'method' => 'errors'),
            'login' => array('match' => '/auth/login', 'method' => 'login'),
            'logout' => array('match' => '/auth/logout', 'method' => 'logout'),
            'vote' => array('match' => '/vote/{author}/{name}', 'method' => 'vote'),
            'package' => array('match' => '/package/{author}/{name}', 'method' => 'package'),
        );
        
        foreach ($routes as $name => $data) {
            $this->application
                ->match($data['match'], array($this, $data['method']))
                ->bind($name);
        }
        
        $this->registerErrorFunction();
    }

    /**
     * Default.
     * 
     * @return string
     */
    public function index()
    {
        return $this->application->render('index.html.twig');
    }

    /**
     * Github oAuth, redirect to use the github strategy.
     */
    public function login()
    {
        return $this->application->redirect('/auth/login/github');
    }
    
    /**
     * Logout clears the session.
     * 
     * @return RedirectResponse
     */
    public function logout()
    {
        $this->session()->clear();
        return $this->application->redirect(
            $this->application['url_generator']->generate('homepage')
        );
    }
    
    /**
     * Shows package info.
     * 
     * @param string $author
     * @param string $name
     * @return string
     */
    public function package($author, $name)
    {
        $repository = $this->application->packages();
        $package = $repository->byAuthorAndName($author, $name);
        return $this->application->render(
            'package.html.twig',
            array(
                'package' => $package,
            )
        );
    }
    
    /**
     * Vote a package.
     * 
     * @param string $author
     * @param string $name
     * @return string
     */
    public function vote($author, $name)
    {
        $package = $this->application->packages()->byAuthorAndName($author, $name);
        return $this->application->render(
            'vote.html.twig',
            array(
                'package' => $package,
            )
        );
    }
    
    /**
     * Renders any errors.
     * 
     * @return string
     */
    public function errors()
    {
        $flashBag = $this->application->session()->getFlashBag();
        $flashBag->add('warning', 'Warning flash message');
        $flashBag->add('info', 'Info flash message');
        $flashBag->add('success', 'Success flash message');
        $flashBag->add('error', 'Error flash message');

        return $this->render('errors.html.twig');
    }
    
    /**
     * 
     * @return void
     */
    protected function registerErrorFunction()
    {
        $app = $this->application;
        $this->application->error(function (\Exception $exception, $code) use ($app) {
            if ($app['debug']) {
                return;
            }

            switch ($code) {
                case 404:
                    $message = 'The requested page could not be found.';
                    break;
                default:
                    $message = 'We are sorry, but something went terribly wrong.';
            }

            return new Response($message, $code);
        });
    }
}
