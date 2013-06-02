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
     * logger instance.
     * 
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * github url
     * 
     * @var string
     */
    protected $githubBaseUrl = 'https://github.com';
    
    /**
     * Client to query the github api.
     * 
     * @var \Github\Client 
     */
    protected $githubClient;
    
    /**
     * Constructor requires a logger instance.
     * 
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
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
        $this->assertClientIsPresent();
        
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
    
    /**
     * Reach for the stars: Scrapes info from the github page.
     * 
     * @param string $username
     * @param string $repository
     */
    public function fromGithubPage($username, $repository)
    {
        $collection = new ArrayCollection(array());
        $url        = '/' . urlencode($username).'/'.urlencode($repository);
        $crawler    = $this->getCrawlerWithContentsFrom($url);
        $nodes      = $crawler->filter('.social-count');
        
        foreach ($nodes as $node) {
            $starred = intval(trim($node->nodeValue));
            $collection->add(MetaInfo::fromValue('community/stargazers', $starred));
            $this->logger->info("Stargazer count fetched from $url:" . $starred);
            break;
        }
        
        return $collection;
    }
    
    /**
     * Scrapes the number of issues 
     * @param string $username
     * @param string $repository
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function fromGithubIssuePage($username, $repository)
    {
        $collection = new ArrayCollection(array());
        $url        = '/' . urlencode($username).'/'.urlencode($repository). '/issues';
        $crawler    = $this->getCrawlerWithContentsFrom($url);
        $nodes      = $crawler->filter('.issues-list-options');
        $nodes      = $nodes->filter('.button-group');
        $nodes      = $nodes->filter('a');
        
        foreach ($nodes as $node) {
            $content = trim($node->nodeValue);
            if (strpos($content, 'Open') !== false) {
                $content = substr($content, 0, strpos($content," "));
                $collection->add(MetaInfo::fromValue('maturity/issues.open', $content));
                $this->logger->info("Open issues fetched from $url:" . $content);
            } elseif (strpos($content, 'Closed') !== false) {
                $content = substr($content, 0, strpos($content," "));
                $collection->add(MetaInfo::fromValue('maturity/issues.closed', $content));
                $this->logger->info("Closed issues fetched from $url:" . $content);
            } 
        }
        
        return $collection;
    }
    
    /**
     * Return the client required for scraping.
     * 
     * @return \Github\HttpClient\HttpClient;
     */
    protected function getHttpClientForScraping()
    {
        $this->assertClientIsPresent();
        
        $client = $this->githubClient->getHttpClient();
        $client->setOption('base_url', $this->githubBaseUrl);
        
        return $client;
    }
    
    /**
     * Returns a dom crawler with page contents.
     * 
     * @param string $url
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function getCrawlerWithContentsFrom($url)
    {
        $client     = $this->getHttpClientForScraping();
        $crawler    = new \Symfony\Component\DomCrawler\Crawler();
        try {
            $result = $client->get($url);
            /* @var $result \Github\HttpClient\Message\Response */
        } catch (\Github\Exception\RuntimeException $exception) {
            $this->logger->alert("Error retrieving info from $url:" . $exception->getMessage());
        }
        
        $crawler->addHtmlContent($result->getContent());
        
        return $crawler;
    }
    
    /**
     * Ensures the github client has been injected.
     * 
     * @throws \RuntimeException
     */
    protected function assertClientIsPresent()
    {
        if ($this->githubClient === null) {
            throw new \RuntimeException('No github client injected.');
        }
    }
}
