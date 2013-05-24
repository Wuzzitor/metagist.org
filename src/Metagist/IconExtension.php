<?php

namespace Metagist;

/**
 * Twig extension to create icons.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class IconExtension extends \Twig_Extension
{
    /**
     * key => icon class mapping
     * @var array
     */
    protected $mapping = array();
    
    /**
     * Init with the mapping to use.
     * 
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }
    
    /**
     * Returns the usable methods.
     * 
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'icon' => new \Twig_Function_Method($this, 'icon', array("is_safe" => array("html"))),
        );
    }
    
    /**
     * Returns a twitter bootstrap icon for a key.
     * 
     * @param string $key
     * @return string
     */
    public function icon($key) 
    {
        if (!array_key_exists($key, $this->mapping)) {
            return '';
        }
        
        $class = $this->mapping[$key];
        return '<i class="' . $class . '"></i>';
    }
    
    /**
     * Returns the name of the extension.
     * 
     * @return string
     */
    public function getName()
    {
        return 'metagist_icons';
    }
}
