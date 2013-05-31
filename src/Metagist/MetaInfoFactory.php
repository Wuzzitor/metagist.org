<?php

namespace Metagist;

use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Factory for MetaInfo objects.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoFactory
{
    /**
     * Client to query the github api.
     * 
     * @var \Github\Client 
     */
    protected $githubClient;
    
    /**
     * Creates metainfos based on a packagist package object.
     * 
     * @param \Packagist\Api\Result\Package $package
     * @return \Doctrine\Common\Collections\Collection
     */
    public function fromPackagistPackage(\Packagist\Api\Result\Package $package)
    {
        $metainfos     = array();
        $versions      = $package->getVersions();
        /* @var $firstVersion \Packagist\Api\Result\Package\Version */
        $firstVersion  = current($versions);
        
        if ($firstVersion != false) {
            $versionString = $firstVersion->getVersion();
            $repository    = $package->getRepository();
            $metainfos = new ArrayCollection(
                array(
                    MetaInfo::fromValue(CategorySchema::REPOSITORY_IDENTIFIER, $repository, $versionString),
                    MetaInfo::fromValue('community/homepage', $firstVersion->getHomepage(), $versionString),
                    MetaInfo::fromValue('reliability/maintainers', count($package->getMaintainers()), $versionString),
                    MetaInfo::fromValue('reliability/requires', count($firstVersion->getRequire()), $versionString),
                    MetaInfo::fromValue('reliability/requires.dev', count($firstVersion->getRequireDev()), $versionString)
                )
            );
            
            $licenses = $firstVersion->getLicense();
            if (is_array($licenses)) {
                $metainfos->add(MetaInfo::fromValue('documentation/license', implode(' ', $licenses), $versionString));
            }
            
            //add additional infos from github
            if ($repository != null) {
                $githubMetainfos = $this->fromGithubRepo($repository);
                foreach ($githubMetainfos as $metainfo) {
                    /* @var $metainfo MetaInfo */
                    $metainfo->setVersion($versionString);
                    $metainfos->add($metainfo);
                }
            }
        }
        
        return $metainfos;
    }
    
    /**
     * Setter to inject a github client for fetching infos from github.
     * 
     * @param \Github\Client $client
     */
    public function setGitHubClient(\Github\Client $client)
    {
        $this->githubClient = $client;
    }
    
    /**
     * Creates metainfos by retrieving repository data from github.
     * 
     * @param string $url repo url
     * @return \Doctrine\Common\Collections\Collection
     */
    public function fromGithubRepo($url)
    {
        if ($this->githubClient === null) {
            throw new \RuntimeException('No github client injected.');
        }
        
        $needle = '://github.com/';
        if (strpos($url, $needle) === FALSE) {
            return null;
        }
        $parts  =  parse_url($url); 
        @list ($owner, $repo) = explode('/', ltrim($parts['path'], '/'));
        if ($owner == '' || $repo == '') {
            return null;
        }
        $owner = strtolower(ltrim($owner, '/'));
        $repo  = strtolower(basename($repo, '.git'));
        
        $collection = new ArrayCollection(array());
        
        $stats = new \Metagist\GithubApi\Stats($this->githubClient);
        $contributors = $stats->contributors($owner, $repo);
        $collection->add(MetaInfo::fromValue('reliability/contributors', count($contributors)));
        $commitCount = 0;
        foreach ($contributors as $contributor) {
            $commitCount += $contributor['total'];
        }
        $collection->add(MetaInfo::fromValue('reliability/commits', $commitCount));
        
        return $collection;
    }
}
