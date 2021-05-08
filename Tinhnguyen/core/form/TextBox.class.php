<?php

namespace Lza\LazyAdmin\Form;


/**
 * Text Box processes HTML Text Fields
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class TextBox extends Input
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <input type="text"
               id="{$id}" name="{$name}"
               class="{$class}" style="{$style}"
               placeholder="{$placeholder}" value="{$value}" />
    ';

    /**
     * @throws
     */
    public function __construct($form, $name, $classes = [], $styles = [], $value = null, $placeholder = '')
    {
        parent::__construct($form, $name, $classes, $styles, $value);
        $this->setPlaceHolder($placeholder);
    }

    /**
     * @throws
     */
    public function setPlaceHolder($placeholder = null)
    {
        $this->data->placeholder = $placeholder;
    }

    /**
     * @throws
     */
    public function getPlaceHolder()
    {
        return $this->data->placeholder;
    }

    /**
     * @throws
     */
    protected function validate($value)
    {
        return !(is_array($value) || is_object($value));
    }
}
