<?php

namespace Lza\LazyAdmin\Utility\Tool;


use Smarty;

/**
 * Smarty Handler handle Smarty Template Engine
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SmartyHandler implements LayoutHandler
{
    /**
     * @var Smarty Library's object
     */
    private $smarty;

    /**
     * @var array List of Smarty's options
     */
    private $options;

    /**
     * @var array List of Classes to be binded to Smarty
     */
    private $classes;

    /**
     * @var boolean Will Content View be minified
     */
    private $minify;

    /**
     * @var boolean Will Smarty use caching
     */
    private $useCaching;

    /**
     * @throws
     */
    public function __construct(
        $templateDir, $compileDir, $options = [], $classes = [],
        $data = [], $minify = false, $useCaching = false
    )
    {
        $this->smarty = new Smarty();

        $this->setTemplateDirectory($templateDir);
        $this->setCompileDirectory($compileDir);
        $this->setOptions($options);
        $this->setClasses($classes);
        $this->setData($data);
        $this->useCaching($useCaching);
        $this->minify($minify);
    }

    /**
     * Set template directory
     *
     * @throws
     */
    public function setTemplateDirectory($templateDir)
    {
        $this->smarty->template_dir = $templateDir;
    }

    /**
     * Set compile directory
     *
     * @throws
     */
    public function setCompileDirectory($compileDir)
    {
        $this->smarty->compile_dir = $compileDir;
    }

    /**
     * Set layout
     *
     * @throws
     */
    public function setContentView($contentView = 'public/PageNotFound.html')
    {
        $this->contentView = $contentView;
    }

    /**
     * Set page options
     *
     * @throws
     */
    public function setOptions($options = [])
    {
        $this->options = $options;
    }

    /**
     * Set layout classes
     *
     * @throws
     */
    public function setClasses($classes = [])
    {
        $this->classes = $classes;
    }

    /**
     * Set layout data
     *
     * @throws
     */
    public function setData($data = [])
    {
        $this->data = $data;
    }

    /**
     * Is page cached?
     *
     * @throws
     */
    public function useCaching($useCaching = true)
    {
        $this->useCaching = $useCaching;
    }

    /**
     * Is layout minified?
     *
     * @throws
     */
    public function minify($minify = true)
    {
        $this->minify = $minify;
    }

    /**
     * Dispay layout on the screen
     *
     * @throws
     */
    public function display($contentView = 'public/PageNotFound.html')
    {
        if ($this->useCaching)
        {
            $this->smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
            $this->smarty->cache_dir = CACHE_PATH . 'smarty/';
            $this->smarty->force_compile = true;
        }

        foreach ($this->options as $key => $value)
        {
            $this->smarty->$key = $value;
        }

        foreach ($this->classes as $key => $value)
        {
            $this->smarty->registerClass($key, $value);
        }

        foreach ($this->data as $key => $value)
        {
            $this->smarty->assign($key, $value);
        }

        if ($this->minify)
        {
            $this->smarty->loadFilter('output', 'trimwhitespace');
        }

        $this->smarty->display($contentView);
        exit();
    }
}
