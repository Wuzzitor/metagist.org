<?php
namespace Metagist;

/**
 * Validator class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class Validator
{
    /**
     * category schema
     * @var CategorySchema 
     */
    private $schema;
    
    /**
     * Constructor.
     * 
     * @param CategorySchema $categorySchema
     * @throws \InvalidArgumentException
     */
    public function __construct(CategorySchema $categorySchema)
    {
        $this->schema = $categorySchema;
    }
    
    /**
     * Validate a name (author or package name).
     * 
     * @param string $name author or package name
     * @return boolean
     */
    public function isValidName($name)
    {
        $pattern = '/^[a-zA-Z0-9\-\.]{2,128}$/i';
        return (bool) preg_match($pattern, $name);
    }
    
    /**
     * Validates a metainfo object against the category schema.
     * 
     * @param MetaInfo $metaInfo
     * @return boolean
     * @throws InvalidInfoException
     */
    public function isValidMetaInfo(MetaInfo $metaInfo)
    {
        if ($metaInfo->getPackage() == null || $metaInfo->getCategory() == null || $metaInfo->getGroup() == null) {
            throw new InvalidInfoException('Package, category or group is not set.');
        }
        
        $type  = $this->schema->getType($metaInfo->getCategory(), $metaInfo->getGroup());
        $value = $metaInfo->getValue();
        
        if ($type == 'string') {
            return is_string($value) && !empty($value);
        }
        
        if ($type == "boolean") {
            return (bool)filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        
        if ($type == "integer") {
            return (bool)filter_var($value, FILTER_VALIDATE_INT);
        }
        
        if ($type == "url") {
            return (bool)filter_var($value, FILTER_VALIDATE_URL);
        }
        
        return false;
    }
}