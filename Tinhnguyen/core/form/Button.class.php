<?php

namespace Lza\LazyAdmin\Form;


use Lza\LazyAdmin\Form\Input;

/**
 * Button processes HTML Buttons
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Button extends Input
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <button type="button"
                id="{$id}" value="{$value}"
                class="{$class}" style="{$style}">
            {$label}
        </button>
    ';

    /**
     * @throws
     */
    public function __construct($form, $name, $label, $value = null, $onclick = '', $classes = [], $styles = [])
    {
        parent::__construct($form, $name, $classes, $styles, $value);
        $this->setLabel($label);
        $this->setOnClick($onclick);
    }

    /**
     * @throws
     */
    public function setLabel($label = null)
    {
        $this->data->label = $label;
    }

    /**
     * @throws
     */
    public function getLabel()
    {
        return $this->data->label;
    }

    /**
     * @throws
     */
    public function setOnClick($callback = '')
    {
        $this->data->onclick = $callback;
    }

    /**
     * @throws
     */
    public function getOnClick()
    {
        return $this->data->onclick;
    }

    /**
     * @throws
     */
    protected function validate($value)
    {
        return !(is_array($value) || is_object($value));
    }

    public function __toString()
    {
        if (strlen($this->data->onclick) > 0)
        {
            $this->contentView .= '
                <script type="text/javascript">
                    $("#{$id}").click({$onclick});
                </script>
            ';
        }
        return parent::__toString();
    }
}
