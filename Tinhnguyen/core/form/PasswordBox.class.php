<?php

namespace Lza\LazyAdmin\Form;


/**
 * Password Box processes HTML Password Fields
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PasswordBox extends TextBox
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <input type="password"
               id="{$id}"  name="{$name}"
               class="{$class}"  style="{$style}"
               placeholder="{$placeholder}" value="{$value}" />
    ';

    /**
     * @throws
     */
    protected function validate($value)
    {
        return !count($this->validator->validatePassword($value));
    }
}
