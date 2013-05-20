<?php
namespace Metagist;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Factory for forms.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class FormFactory
{
    /**
     * the categories
     * @var CategorySchema 
     */
    private $schema;
   
    /**
     * symfony form factory
     * @var Symfony\Component\Form\FormFactory 
     */
    private $formFactory;
    
    /**
     * Constructor.
     * 
     * @param \Symfony\Component\Form\FormFactory $factory
     * @param \Metagist\CategorySchema           $schema
     */
    public function __construct(\Symfony\Component\Form\FormFactory $factory, CategorySchema $schema)
    {
        $this->formFactory = $factory;
        $this->schema      = $schema;
    }
    
    /**
     * Returns the rating form.
     * 
     * @return \Symfony\Component\Form\Form
     */
    public function getRateForm(array $versions = array(''))
    {
        $builder = $this->formFactory->createBuilder('form');

        $form = $builder
            ->add('rating', 'choice', array(
                'choices' => range(1, 5),
                'constraints' => new Assert\Range(array('min' => 1, 'max' => 5)),
            ))
            ->add('version', 'choice', array(
                'choices' => array('') + $versions,
                'multiple' => false,
                'expanded' => false
            ))
            ->add('title', 'text', array(
                'constraints' => new Assert\NotBlank(),
            ))
            ->add('comment', 'textarea')
            ->getForm()
        ;
        
        return $form;
    }
    
    /**
     * Returns the form for metainfo contribution.
     * 
     * @param array  $versions
     * @param string $type
     * @return \Symfony\Component\Form\Form
     */
    public function getContributeForm(array $versions = array(''), $type = 'string')
    {
        $types = array(
            'url' => 'text',
            'integer' => 'number'
        );
        if (isset($types[$type])) {
            $fieldType = $types[$type];
        } else {
            $fieldType = 'text';
        }
        
        $builder = $this->formFactory->createBuilder('form');
        $form = $builder
            ->add('version', 'choice', array(
                'choices' => array('') + $versions,
                'multiple' => false,
                'expanded' => false
            ))
            ->add('value', $fieldType, array(
                'constraints' => new Assert\NotBlank(),
            ))
            ->getForm()
        ;
        
        return $form;
    }
}