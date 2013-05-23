<?php
namespace Metagist;

use \Symfony\Component\Security\Core\SecurityContextInterface;
use \Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Security proxy for the metainfo repo.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoRepositoryProxy
{
    /**
     * MetaInfo Repo
     * 
     * @var \Metagist\MetaInfoRepository 
     */
    private $repository;
    
    /**
     * The security context (controls access).
     * 
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $context;
    
    /**
     * The category schema
     * 
     * @var \Metagist\CategorySchema
     */
    private $schema;
    
    /**
     * Constructor.
     * 
     * @param \Metagist\MetaInfoRepository $repo
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $context
     * @param \Metagist\CategorySchema $schema
     */
    public function __construct(
        MetaInfoRepository $repo,
        SecurityContextInterface $context,
        CategorySchema $schema
    ) {
        $this->repository = $repo;
        $this->context    = $context;
        $this->schema     = $schema;
    }
    
    /**
     * Forwarding method.
     * 
     * @param string $name
     * @param array  $arguments
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->repository, $name), $arguments);
    }
    
    /**
     * Controls the access to the save() method.
     * 
     * @param \Metagist\MetaInfo $metaInfo
     * @throws AccessDeniedException
     */
    public function save(MetaInfo $metaInfo)
    {
        $category   = $metaInfo->getCategory();
        $group      = $metaInfo->getGroup();
        $reqRole    = $this->schema->getAccess($category, $group);
        if (!$this->context->isGranted($reqRole)) {
            throw new AccessDeniedException(
                $reqRole . ' is not authorized to save ' . $category . "/" . $group
            );
        }
        
        //cardinality check
        $groups      = $this->schema->getGroups($category);
        $groupData   = $groups[$group];
        $cardinality = isset($groupData->cardinality) ? : null;
        
        $this->repository->save($metaInfo, $cardinality);
    }
}