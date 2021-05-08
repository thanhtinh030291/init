<?php

namespace Lza\LazyAdmin\Form;


/**
 * Html Element
 * Abstract Class for any Html Elements
 *
 * @var csrf
 * @var datetime
 * @var encryptor
 * @var env
 * @var i18n
 * @var logger
 * @var request
 * @var session
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
abstract class HtmlElement
{
    /**
     * @var object Element's meta data
     */
    protected $data;

    /**
     * @var string Contents of the control
     */
    protected $contentView = '';

    /**
     * @var string Contents of the control
     */
    protected $contentScript = '';

    /**
     * @throws
     */
    public function __construct($name)
    {
        $this->data = (object) [];
        $this->data->id = chain_case($name);
        $this->data->name = snake_case($name);
    }

    /**
     * @throws
     */
    public function getId()
    {
        return $this->data->id;
    }

    /**
     * @throws
     */
    public function setId($id)
    {
        $this->data->id = $id;
    }

    /**
     * @throws
     */
    public function getName()
    {
        return $this->data->name;
    }

    /**
     * @throws
     */
    public function setName($name)
    {
        $this->data->name = $name;
    }

    /**
     * @throws
     */
    public function setContentView($path)
    {
        $this->contentView = file_get_contents($path);
    }

    /**
     * @throws
     */
    public function getContentView()
    {
        return $this->contentView;
    }

    /**
     * @throws
     */
    public function setContentScript($path)
    {
        $this->contentScript = file_get_contents($path);
    }

    /**
     * @throws
     */
    public function getContentScript()
    {
        $contentScript = $this->contentScript;
        foreach ($this->data as $key => $value)
        {
            if (!is_array($value) && !is_object($value))
            {
                $contentScript = str_replace('{$' . $key . '}', $value, $contentScript);
            }
        }
        return $contentScript;
    }
}
