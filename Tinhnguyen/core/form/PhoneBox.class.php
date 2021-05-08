<?php

namespace Lza\LazyAdmin\Form;


/**
 * Phone Box processes HTML Text Fields for phones
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PhoneBox extends TextBox
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <input type="tel" pattern="{$pattern}"
               id="{$id}" name="{$name}"
               class="{$class}" style="{$style}"
               placeholder="{$placeholder}" value="{$value}" />
    ';

    /**
     * @throws
     */
    public function __construct(
        $form, $name, $classes = [],
        $styles = [], $value = null, $pattern = null
    )
    {
        parent::__construct($form, $name, $classes, $styles, $value);
        $this->setPattern($pattern);
    }

    /**
     * @throws
     */
    public function getPattern()
    {
        return $this->data->pattern;
    }

    /**
     * @throws
     */
    public function setPattern($pattern)
    {
        $this->data->pattern = $pattern;
    }

    /**
     * @throws
     */
    protected function validate($value)
    {
        return !isset($this->data->pattern)
            ? true : preg_match(
                "/^{$this->data->pattern}$/",
                $value
            );
    }
}
