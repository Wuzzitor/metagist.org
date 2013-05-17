<?php

namespace Metagist;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

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
            'rate' => array('match' => '/rate/{author}/{name}', 'method' => 'rate'),
            'contriubute' => array('match' => '/contribute/{author}/{name}', 'method' => 'contribute'),
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
        $repo = $this->application->metainfo();
        return $this->application->render(
            'index.html.twig', array(
                'latest' => $repo->latest(),
                'featured' => $repo->byCategoryGroup('reviews', 'featured'),
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
        $metaInfoRepo = $this->application[RepoProvider::METAINFO_REPO];
        $metaInfos = $metaInfoRepo->byPackage($package);
        $package->setMetaInfos($metaInfos);

        return $this->application->render(
                'package.html.twig', array(
                'package' => $package,
                'categories' => $this->application->categories()
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
    public function rate($author, $name)
    {
        $package = $this->application->packages()->byAuthorAndName($author, $name);
        return $this->application->render(
            'rate.html.twig', array(
                'package' => $package,
            )
        );
    }

    /**
     * Contribute to the package (provide information).
     * 
     * @param string $author
     * @param string $name
     * @return string
     */
    public function contribute($author, $name, Request $request)
    {
        $package = $this->application->packages()->byAuthorAndName($author, $name);
        $form    = $this->getContributeForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $app['session']->getFlashBag()->add('success', 'The form is valid');
            } else {
                $form->addError(new FormError('This is a global error'));
                $app['session']->getFlashBag()->add('info', 'The form is bind, but not valid');
            }
        }


        return $this->application->render(
                'contribute.html.twig', array(
                'package' => $package,
                'form' => $form->createView(),
                )
        );
    }
    
    /**
     * Returns the form for metainfo contribution.
     * 
     * @return \Symfony\Component\Form\Form
     */
    protected function getContributeForm()
    {
        $builder = $this->application['form.factory']->createBuilder('form');
        $choices = array('choice a', 'choice b', 'choice c');

        $form = $builder
            ->add(
                $builder->create('sub-form', 'form')
                ->add('subformemail1', 'email', array(
                    'constraints' => array(new Assert\NotBlank(), new Assert\Email()),
                    'attr' => array('placeholder' => 'email constraints'),
                    'label' => 'A custom label : ',
                ))
                ->add('subformtext1', 'text')
            )
            ->add('text1', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'attr' => array('placeholder' => 'not blank constraints')
            ))
            ->add('text2', 'text', array('attr' => array('class' => 'span1', 'placeholder' => '.span1')))
            ->add('text3', 'text', array('attr' => array('class' => 'span2', 'placeholder' => '.span2')))
            ->add('text4', 'text', array('attr' => array('class' => 'span3', 'placeholder' => '.span3')))
            ->add('text5', 'text', array('attr' => array('class' => 'span4', 'placeholder' => '.span4')))
            ->add('text6', 'text', array('attr' => array('class' => 'span5', 'placeholder' => '.span5')))
            ->add('text8', 'text', array('disabled' => true, 'attr' => array('placeholder' => 'disabled field')))
            ->add('textarea', 'textarea')
            ->add('email', 'email')
            ->add('integer', 'integer')
            ->add('number', 'number')
            ->add('password', 'password')
            ->add('percent', 'percent')
            ->add('search', 'search')
            ->add('url', 'url')
            ->add('choice1', 'choice', array(
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true
            ))
            ->add('choice2', 'choice', array(
                'choices' => $choices,
                'multiple' => false,
                'expanded' => true
            ))
            ->add('choice3', 'choice', array(
                'choices' => $choices,
                'multiple' => true,
                'expanded' => false
            ))
            ->add('choice4', 'choice', array(
                'choices' => $choices,
                'multiple' => false,
                'expanded' => false
            ))
            ->add('time', 'time')
            ->add('checkbox', 'checkbox')
            ->add('radio', 'radio')
            ->add('password_repeated', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => array('required' => true),
                'first_options' => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->getForm()
        ;
        
        return $form;
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
            $package = $this->application[RepoProvider::PACKAGE_FACTORY]->byAuthorAndName($author, $name);
            if ($packageRepo->save($package)) {
                /* @var $metaInfoRepo MetaInfoRepository */
                $metaInfoRepo = $this->application[RepoProvider::METAINFO_REPO];
                $metaInfoRepo->savePackage($package);
            }
        }

        return $package;
    }

}
