<?php

namespace Lza\LazyAdmin\Form;


/**
 * Check Box processes HTML Check Boxes
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class CheckBox extends Input
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <input type="checkbox"
               id="{$id}" name="{$name}"
               class="{$class}" style="{$style}"
               {$checked} value="{$value}" />
    ';

    /**
     * @throws
     */
    public function __construct($form, $name, $classes = [], $styles = [], $value = null, $isChecked = false)
    {
        parent::__construct(
            $form, $name, $classes, $styles, $value
        );
        $this->setChecked($isChecked);
    }

    /**
     * @throws
     */
    protected function validate($value)
    {
        return !(is_array($value) || is_object($value));
    }

    /**
     * @throws
     */
    public function setChecked($isChecked)
    {
        $this->data->checked = $isChecked
                ? 'checked' : '';
    }

    /**
     * @throws
     */
    public function getChecked()
    {
        return $this->isChecked;
    }
}
