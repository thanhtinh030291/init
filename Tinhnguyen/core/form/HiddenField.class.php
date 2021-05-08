<?php

namespace Lza\LazyAdmin\Form;


/**
 * Hidden Fields processes HTML Hidden Fields
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class HiddenField extends Input
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '<input type="hidden" value="{$value}" id="{$id}" name="{$name}" />';

    /**
     * @throws
     */
    public function __construct($form, $name, $value = null)
    {
        parent::__construct($form, $name, null, null, $value);
    }

    /**
     * @throws
     */
    protected function validate($value)
    {
        return !(is_array($value) || is_object($value));
    }
}
