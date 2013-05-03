<?php
namespace Metagist;

/**
 * WebController for Metagist.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class WebController extends WebControllerAbstract
{
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
            //'loginCallback' => array('match' => '/auth/callback', 'method' => 'oAuthCallback'),
            //'logout' => array('match' => '/auth/logout', 'method' => 'logout'),
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
        return $this->render('index.html.twig');
    }

    /**
     * Github oAuth.
     * 
     * 
     */
    public function login()
    {
    }
    
    /**
     * oAuth callback action.
     * 
     * 
     */
    public function oAuthCallback()
    {
        
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
     * Renders any errors.
     * 
     * @return string
     */
    public function errors()
    {
        $flashBag = $this->session()->getFlashBag();
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
