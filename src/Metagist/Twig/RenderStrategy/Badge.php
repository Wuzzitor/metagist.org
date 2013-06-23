<?php
namespace Metagist\Twig\RenderStrategy;

/**
 * Strategy to render metainfo images like badges.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class Badge implements StrategyInterface
{
    /**
     * Renders the metainfo value as badge / image.
     * 
     * @param \Metagist\MetaInfo $metaInfo
     * @return string
     */
    public function render(\Metagist\MetaInfo $metaInfo)
    {
        $template = '<img src="%s" alt="badge for %s"/>';
        return sprintf(
            $template,
            $metaInfo->getValue(),
            $metaInfo->getGroup()
        );
    }

}