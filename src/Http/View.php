<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Core
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Http;

use Kerisy\Core\Set;
use Symfony\Component\Translation\Exception\InvalidResourceException;

class View extends Set
{
    protected $_ext = '.phtml';
    protected $_template_dir;
    protected $_prefix;

    public function __construct($prefix = 'front')
    {
        $this->_prefix = $prefix;
        
        $this->_template_dir = APPLICATION_PATH . 'views/' . strtolower($prefix) . '/';
        if (!is_dir($this->_template_dir))
        {
            throw new InvalidResourceException('Template directory does not exist: ' . $this->_template_dir);
        }
    }

    private function getTemplateFile($template)
    {
        $template_file = $this->_template_dir . $template .$this->_ext;

        if(!file_exists($template_file)){
            throw new InvalidResourceException('Template file does not exist: ' . $template_file);
        }

        return $template_file;
    }
    
    public function getTplPath($template)
    {
        return strtolower($this->_prefix) . '/'. $template;
    }

    private function template($template)
    {

        include $this->getTemplateFile($template);
    }

    public function render($template,$data = [])
    {
        ob_start(null, 0, false);

        include $this->getTemplateFile($template);

        return ob_get_contents();
    }
}
