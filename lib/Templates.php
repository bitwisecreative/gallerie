<?php
/**
 * A simple template class
 */

class Templates
{
    /**
     * @var string The templatesdirectory
     */
    private $dir;
    /**
     * @var array The template variables
     */
    private $vars = array();

    /**
     * Constructor
     * @param $dir string The templates directory
     * @throws Exception
     */
    public function __construct($dir)
    {
        if (!is_dir($dir)) {
            throw new Exception("The supplied template directory is not a directory.");
        }
        if (!is_readable($dir)) {
            throw new Exception("The supplied template directory is not readable.");
        }
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }
        $this->dir = $dir;
    }

    /**
     * Magic template variable getter
     * @param $name string The template variable
     * @return mixed
     */
    public function __get($name)
    {
        return $this->vars[$name];
    }

    /**
     * Magic template variable setter
     * @param $name string The template variable name
     * @param $value mixed The template variable value
     * @throws Exception
     */
    public function __set($name, $value)
    {
        if ($name == 'template_file') {
            throw new Exception("Cannot bind variable named 'template_file'");
        }
        $this->vars[$name] = $value;
    }

    /**
     * Template renderer
     * @param $template_file string The template file
     * @return string
     * @throws Exception
     */
    public function render($template_file)
    {
        if (array_key_exists('template_file', $this->vars)) {
            throw new Exception("Cannot bind variable called 'template_file'");
        }
        if (substr($template_file, -4) != '.php') {
            $template_file .= '.php';
        }
        if (!is_readable($this->dir . $template_file)) {
            throw new Exception("Template file is not readable.");
        }
        extract($this->vars);
        ob_start();
        include $this->dir . $template_file;
        return ob_get_clean();
    }
}
