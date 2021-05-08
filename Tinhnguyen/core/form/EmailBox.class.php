<?php

namespace Lza\LazyAdmin\Form;


/**
 * Email Box processes HTML Email Fields
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class EmailBox extends TextBox
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <input type="email"
               id="{$id}" name="{$name}"
               class="{$class}" style="{$style}"
               placeholder="{$placeholder}" value="{$value}" />
    ';

    /**
     * @throws
     */
    protected function validate($value)
    {
        return $this->validator->validateEmail($value);
    }
}
