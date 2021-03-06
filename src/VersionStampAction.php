<?php
//no namespace!

use Liip\RMT\Action\BaseAction;
use Liip\RMT\Context;

/**
 * RMT Action that writes the current version into a file as constant.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class VersionStampAction extends BaseAction
{
    /**
     * configurable options.
     * 
     * @var array
     */
    protected $options = array();

    /**
     * Constructor.
     * 
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = array_merge(
            array('file' => 'version.php'), $options
        );
    }
    
    /**
     * Writes the current version to the target file.
     * 
     * @return void
     */
    public function execute()
    {
        $version = Context::getInstance()->getParameter('new-version');
        if ($version == null) {
            throw new \RuntimeException('Could not determine the new version.');
        }
        
        $template = "<?php\ndefine('METAGIST_VERSION', '%s');\n";

        file_put_contents($this->options['file'], sprintf($template, $version));
    }
}