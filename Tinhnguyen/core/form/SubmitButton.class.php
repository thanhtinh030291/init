<?php

namespace Lza\LazyAdmin\Form;


use Lza\LazyAdmin\Form\Button;

/**
 * Submit Button processes HTML Submit Button
 *
 * @var request
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SubmitButton extends Button
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <button type="submit"
                id="{$id}" name="action"
                class="{$class}" style="{$style}"
                value="{$value}">
            {$label}
        </button>
    ';

    /**
     * @var string Closure to be used on JS when the Form that hold this control submited
     */
    protected $onSubmit;

    /**
     * @throws
     */
    public function __construct(
        $form, $name, $label, $value, $onsubmit = null, $onclick = '', $classes = [], $styles = []
    )
    {
        parent::__construct($form, $name, $label, $value, $onclick, $classes, $styles);
        $this->setName('action');
        $this->setOnSubmit($onsubmit);
    }

    /**
     * @throws
     */
    public function setOnSubmit($onsubmit)
    {
        $this->onSubmit = $onsubmit;
    }

    /**
     * @throws
     */
    public function getOnSubmit()
    {
        return $this->onSubmit;
    }

    /**
     * @throws
     */
    public function onSubmit()
    {
        if (
            isset($this->request->{$this->data->name}) &&
            $this->request->{$this->data->name} === $this->data->value &&
            $this->onSubmit !== null
        )
        {
            $callback = $this->onSubmit;
            $callback();
        }
    }
}
