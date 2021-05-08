<?php

namespace Lza\LazyAdmin\Form;


/**
 * Number Box processes HTML Number Fields
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class NumberBox extends TextBox
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <input type="number"
               id="{$id}" name="{$name}"
               class="{$class}" style="{$style}"
               placeholder="{$placeholder}" value="{$value}" />
    ';

    /**
     * @throws
     */
    protected function validate($value)
    {
        return $this->validator->validateNumber($value);
    }
}
