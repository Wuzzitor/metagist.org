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
     * Plain output of the collection. Is fallback strategy.
     * 
     * @var string
     */
    const STRATEGY_UNMODIFIED = 'unmodifiedList';
    
    /**
     * "category/group" indentifier => render strategy
     * 
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
        foreach ($mapping as $strategy) {
            if (!method_exists($this, $strategy)) {
                throw new \InvalidArgumentException('Unknown strategy ' . $strategy);
            }
        }
        $this->mapping = $mapping;
    }
    
    /**
     * Renders a collection of metainfos according to the configuration.
     * 
     * @param \Doctrine\Common\Collections\Collection $collection
     */
    public function renderInfos(Collection $collection)
    {
        /* @var $first \Metagist\MetaInfo */
        $first    = $collection->get(0);
        $category = $first->getCategory();
        $group    = $first->getGroup();
        
        $strategy = isset($this->mapping[$category.'/'.$group]) ? 
            $this->mapping[$category.'/'.$group] : self::STRATEGY_UNMODIFIED;
        return $this->$strategy($collection);
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
     * Unmodified rendering of the metainfo values as a list.
     * 
     * @param \Doctrine\Common\Collections\Collection $collection
     * @return string
     */
    protected function unmodifiedList(Collection $collection)
    {
        $buffer = '<ul>';
        foreach ($collection as $metaInfo) {
            $buffer .= '<li>' . $metaInfo->getValue() . '</li>' . PHP_EOL;
        }
        $buffer .= '</ul>' . PHP_EOL;
        
        return $buffer;
    }
}