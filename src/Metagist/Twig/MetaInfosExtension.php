<?php

namespace Metagist\Twig;

use \Doctrine\Common\Collections\Collection;

/**
 * Twig extension to render a collection of metainfos.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfosExtension extends \Twig_Extension
{
    /**
     * name of the extension
     * @var string
     */
    const NAME = 'metainfos_extension';
    
    /**
     * Config key to apply a css class.
     * 
     * @var string
     */
    const CSS_CLASS_KEY = "class";
    
    /**
     * config key to change the way to info is displayed (e.g. as url or badge)
     * 
     * @var string
     */
    const CONVERSION_KEY = 'displayAs';
    
    /**
     * config key to provide a collection filter callback. The callback
     * must accept a collection as argument and return a collection.
     * 
     * @var string
     */
    const FILTER_KEY = 'filter';
    
    /**
     * "category/group" indentifier => render strategy
     * 
     * @var array
     */
    protected $mappings = array();
    
    /**
     * Init with the mapping to use.
     * 
     * @param array $mappings
     */
    public function __construct(array $mappings)
    {
        $this->mappings = $mappings;
    }
    
    /**
     * Renders a collection of metainfos according to the configuration.
     * 
     * @param \Doctrine\Common\Collections\Collection $collection
     */
    public function renderInfos(Collection $collection)
    {
        if ($collection->count() == 0) {
            return '';
        }
        
        /* @var $first \Metagist\MetaInfo */
        $first    = $collection->first();
        $category = $first->getCategory();
        $group    = $first->getGroup();
        
        $strategy = isset($this->mappings[$category.'/'.$group]) ? 
            $this->mappings[$category.'/'.$group] : array();
        return $this->renderList($collection, $strategy);
    }
    
    /**
     * Returns the names of the provided functions.
     * 
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'renderInfos' => new \Twig_Function_Method($this, 'renderInfos', array("is_safe" => array("html"))),
            'renderInfo' => new \Twig_Function_Method($this, 'renderInfo', array("is_safe" => array("html"))),
        );
    }
    
    /**
     * Returns the name of the extension.
     * 
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
    
    /**
     * Renders the metainfo values as a list.
     * 
     * @param \Doctrine\Common\Collections\Collection $collection
     * @param array $config
     * @return string
     */
    protected function renderList(Collection $collection, array $config)
    {
        if (isset($config[self::FILTER_KEY]) && $config[self::FILTER_KEY] !== null) {
            $function = $config[self::FILTER_KEY];
            $collection = $function($collection);
        }
        
        
        $buffer = '<ul class="unstyled">';
        foreach ($collection as $metaInfo) {
            $buffer .= '<li>' . $this->renderMetaInfo($metaInfo, $config). '</li>' . PHP_EOL;
        }
        $buffer .= '</ul>' . PHP_EOL;
        
        return $buffer;
    }
    
    public function renderInfo(\Metagist\MetaInfo $metaInfo)
    {
        /* @var $first \Metagist\MetaInfo */
        $category = $metaInfo->getCategory();
        $group    = $metaInfo->getGroup();
        
        $strategy = isset($this->mappings[$category.'/'.$group]) ? 
            $this->mappings[$category.'/'.$group] : array();
        
        return $this->renderMetaInfo($metaInfo, $strategy);
    }
    
    /**
     * Renders a single metainfo.
     * 
     * @param \Metagist\MetaInfo $metaInfo
     * @param array $config
     * @return string
     */
    protected function renderMetaInfo(\Metagist\MetaInfo $metaInfo, array $config)
    {
        $class = isset($config[self::CSS_CLASS_KEY]) ? 
            ' class="' . $config[self::CSS_CLASS_KEY] . '"' : '';
        $displayAs = isset($config[self::CONVERSION_KEY]) ? $config[self::CONVERSION_KEY] : null;
        
        return '<span' . $class . '>' . $this->getRenderedValue($metaInfo, $displayAs) . '</span>';
    }
    
    /**
     * Renders the metainfo using a template.
     * 
     * @param \Metagist\MetaInfo $metaInfo
     * @param string                  $displayAs
     * @return string
     */
    protected function getRenderedValue(\Metagist\MetaInfo $metaInfo, $displayAs = null)
    {
        $value = $metaInfo->getValue();
        
        $conversions = array(
            'url' => '<a href="%1$s" target="_blank">%1$s</a>',
            'badge' => '<img src="%s" alt="badge"/>',
        );
        if (isset($conversions[$displayAs])) {
            $template = $conversions[$displayAs];
        } else {
            $template = '%s';
        }
        
        return sprintf($template, $value);
    }
    
}