<?php
namespace Metagist\Twig\RenderStrategy;

/**
 * Strategy to render metainfo links.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class Link implements StrategyInterface
{
    /**
     * Renders the metainfo value as link.
     * @param \Metagist\MetaInfo $metaInfo
     * @return string
     */
    public function render(\Metagist\MetaInfo $metaInfo)
    {
        $template = '<a href="%1$s" target="_blank">%1$s</a>';
        return sprintf($template, $metaInfo->getValue());
    }

}