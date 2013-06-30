<?php

namespace Metagist;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\Collection;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrapView;

/**
 * WebController for Metagist.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class WebController extends Controller
{
    /**
     * Routing setup.
     * 
     * 
     */
    protected function initRoutes()
    {
        $routes = array(
            'homepage'      => array('match' => '/', 'method' => 'index'),
            'errors'        => array('match' => '/errors', 'method' => 'errors'),
            'loginNotice'   => array('match' => '/login', 'method' => 'loginNotice'),
            'login'         => array('match' => '/auth/login', 'method' => 'login'),
            'logout'        => array('match' => '/auth/logout', 'method' => 'logout'),
            'ratings'       => array('match' => '/ratings/{author}/{name}', 'method' => 'ratings'),
            'ratings-pp'    => array('match' => '/ratings/{author}/{name}/{page}', 'method' => 'ratings'),
            'rate'          => array('match' => '/rate/{author}/{name}', 'method' => 'rate'),
            'contribute-list' => array('match' => '/contribute/list/{author}/{name}', 'method' => 'contributeList'),
            'contribute'    => array('match' => '/contribute/{author}/{name}/{category}/{group}', 'method' => 'contribute'),
            'package'       => array('match' => '/package/{author}/{name}', 'method' => 'package'),
            'search'        => array('match' => '/search', 'method' => 'search'),
            'search-page'   => array('match' => '/search/{query}/{page}', 'method' => 'search'),
            'update'        => array('match' => '/update/{author}/{name}', 'method' => 'update'),
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
        $repo    = $this->application->metainfo();
        $ratings = $this->application->ratings();
        return $this->application->render(
            'index.html.twig', array(
                'latest' => $repo->latest(),
                'latestRating' => $ratings->latest()->first(),
                'featured' => $repo->byGroup('featured'),
                'best' => $this->application->ratings()->best(5)
            )
        );
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
        $this->application->session()->invalidate();
        $this->application->session()->clear();
        
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
        $package = $this->getPackage($author, $name);
        //retrieve the related infos.
        $metaInfos = $this->application->metainfo()->byPackage($package);
        $package->setMetaInfos($metaInfos);
        

        return $this->application->render(
            'package.html.twig',
            array(
                'package' => $package,
                'categories' => $this->application->categories(),
                'ratings' => $this->application->ratings()->byPackage($package, 0, 5)
            )
        );
    }
    
    /**
     * Updates package info by invoking the worker.
     * 
     * @param string $author
     * @param string $name
     * @return string
     */
    public function update($author, $name)
    {
        $flashBag = $this->application->session()->getFlashBag();
        try {
            $package = $this->getPackage($author, $name);
            $this->application->getApi()->worker()->scan($author, $name);
        } catch (\Exception $exception) {
            $flashBag->add(
                'error',
                'Error while updating the package: ' . $exception->getMessage()
            );
            $this->application->logger()->error('Exception: ' . $exception->getMessage());
            return $this->application->redirect('/');
        }
        
        $flashBag->add(
            'success',
            'The package ' . $package->getIdentifier() . ' will be updated. Thanks.'
        );
        return $this->application->redirect('/package/' . $package->getIdentifier());
    }

    /**
     * Shows the package ratings.
     * 
     * @param sting  $author
     * @param string $name
     * @return string
     */
    public function ratings($author, $name, $page = 1)
    {
        $package  = $this->application->packages()->byAuthorAndName($author, $name);
        $ratings  = $this->application->ratings()->byPackage($package);
        $routeGen = function($page) { return '/ratings/'.$page;};
        $pager    = $this->getPaginationFor($ratings);
        $pager->setCurrentPage($page);
        $view     = new TwitterBootstrapView();
        return $this->application->render(
            'ratings.html.twig', array(
                'package' => $package,
                'ratings' => $pager,
                'pagination' => $view->render($pager, $routeGen)
            )
        );
    }
    
    /**
     * Rate a package.
     * 
     * @param string $author
     * @param string $name
     * @return string
     */
    public function rate($author, $name, Request $request)
    {
        $package  = $this->application->packages()->byAuthorAndName($author, $name);
        $flashBag = $this->application->session()->getFlashBag();
        $user     = $this->application->security()->getToken()->getUser();
        $rating   = $this->application->ratings()->byPackageAndUser($package, $user);
        $form     = $this->getFormFactory()->getRateForm($package->getVersions(), $rating);
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data     = $form->getData();
                $data['package'] = $package;
                $data['user_id'] = $user->getId();
                $rating = Rating::fromArray($data);
                $this->application->ratings()->save($rating);
                $flashBag->add('success', 'Thanks.');
                return $this->application->redirect('/package/' . $package->getIdentifier());
            } else {
                $form->addError(new FormError('Please check the entered value.'));
            }
        }
        
        return $this->application->render(
            'rate.html.twig', array(
                'package' => $package,
                'form'    => $form->createView()
            )
        );
    }

    /**
     * Lists the categories and groups to contribute to.
     * 
     * @param string $author
     * @param string $name
     * @return string
     */
    public function contributeList($author, $name)
    {
        $package = $this->application->packages()->byAuthorAndName($author, $name);
        //retrieve the related infos.
        $metaInfos = $this->application->metainfo()->byPackage($package);
        $package->setMetaInfos($metaInfos);
        
        return $this->application->render(
            'contribute-list.html.twig', 
            array(
                'package' => $package,
                'categories' => $this->application->categories()
            )
        );
    }
    
    /**
     * Contribute to the package (provide information).
     * 
     * @param string  $author
     * @param string  $name
     * @param string  $category
     * @param string  $group
     * @param Request $request
     * @return string
     */
    public function contribute($author, $name, $category, $group, Request $request)
    {
        $package     = $this->application->packages()->byAuthorAndName($author, $name);
        $flashBag    = $this->application->session()->getFlashBag();
        $groups      = $this->application->categories()->getGroups($category);
        $groupData   = $groups[$group];
        $form        = $this->getFormFactory()->getContributeForm(
                            $package->getVersions(), $groupData->type
                       );
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data     = $form->getData();
                $metaInfo = MetaInfo::fromValue($category.'/'.$group, $data['value'], $data['version']);
                $metaInfo->setPackage($package);
                
                try {
                    $this->application->metainfo()->save($metaInfo);
                    $flashBag->add('success', 'Info saved. Thank you.');
                } catch (Symfony\Component\Security\Core\Exception\AccessDeniedException $exception) {
                    $this->application->logger()->warn($exception->getMessage());
                    $flashBag->add('error', 'Access denied to ' . $category . '_' . $group);
                }
                
                return $this->application->redirect('/package/' . $package->getIdentifier());
            } else {
                $form->addError(new FormError('Please check the entered value.'));
            }
        }


        return $this->application->render(
            'contribute.html.twig', 
            array(
                'package' => $package,
                'form' => $form->createView(),
                'category' => $category,
                'group' => $group,
                'type'  => $groupData->type,
                'description' => $groupData->description,
            )
        );
    }
    
    /**
     * Search for a package.
     * 
     * @param Request $request
     * @return string
     */
    public function search(Request $request)
    {
        $query = $request->get('query');
        if ($query == '*') {
            $query = '';
        }
        $page  = $request->get('page');
        if (intval($page) == 0) {
            $page = 1;
        }
        @list ($author, $name) = explode('/', $query);
        $package = null;
        try {
            $package = $this->application->packages()->byAuthorAndName($author, $name);
            if ($package !== null) {
                $url = '/' . $package->getIdentifier();
                return new \Symfony\Component\HttpFoundation\RedirectResponse($url);
            } else {
                /*
                 * Creating a dummy package, triggers the creation process if
                 * user follows the link.
                 */
                $dummy = new Package($author . '/' . $name);
            }
        } catch (\Exception $exception) {
            $this->application->logger()->info('Search failed: ' . $exception->getMessage());
        }
        
        $packages = $this->application->packages()->byIdentifierPart($author);
        
        $routeGenerator = function($page) use ($query) {
            if ($query == '') {
                $query = '*';
            }
            return '/search/' . urlencode($query) . '/'.$page;
        };
        $pagerfanta = $this->getPaginationFor($packages);
        $pagerfanta->setCurrentPage($page);
        $view       = new TwitterBootstrapView();
        
        return $this->application->render(
            'search.html.twig', 
            array(
                'query' => $query,
                'dummy' => isset($dummy) ? $dummy : null,
                'packages' => $pagerfanta,
                'pagination' => $view->render($pagerfanta, $routeGenerator)
            )
        );
    }
    
    /**
     * Just displays the notice that the user has to be logged in.
     * 
     * @return string
     */
    public function loginNotice()
    {
        return $this->application->render('login.html.twig');
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

    /**
     * Returns the form factory.
     * 
     * @return \Metagist\FormFactory
     */
    protected function getFormFactory()
    {
        return new FormFactory(
            $this->application['form.factory'],
            $this->application[ServiceProvider::CATEGORY_SCHEMA]
        );
    }
    
    /**
     * Creates a pagination for the given collection.
     * 
     * @param \Doctrine\Common\Collections\Collection $collection
     * @return Pagerfanta
     */
    protected function getPaginationFor(Collection $collection, $maxPerPage = 25)
    {
        $adapter = new DoctrineCollectionAdapter($collection);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        return $pagerfanta;
    }
}
