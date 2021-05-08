<?php

namespace Lza\LazyAdmin\Form;


use Exception;
use Lza\LazyAdmin\Exception\SetInputValueException;
use Lza\LazyAdmin\Form\HtmlElement;

/**
 * Input
 * Abstract class for any HTML Inputs
 *
 * @var validator
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
abstract class Input extends HtmlElement
{
    /**
     * @var Form The Form that hold this control
     */
    protected $form;

    /**
     * @var array CSS Classes of the controls
     */
    protected $classes;

    /**
     * @var array Inline CSS Styles of the control
     */
    protected $styles;

    /**
     * @throws
     */
    public function __construct($form, $name, $classes = [], $styles = [], $value = null)
    {
        parent::__construct($name);
        $this->data->value = null;

        $this->setClasses($classes);
        $this->setStyles($styles);
        $this->setForm($form);
        $this->setValue($value);
    }

    /**
     * @throws
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * @throws
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @throws
     */
    public function setValue($value)
    {
        if (!$this->validate($value))
        {
            throw new SetInputValueException(
                "Failed to set value " . json_encode($value) .
                " to " . get_class($this) . ": {$this->data->name}!"
            );
        }
        $this->data->value = $value;
    }

    /**
     * @throws
     */
    public function getValue()
    {
        return $this->data->value;
    }

    /**
     * @throws
     */
    protected function validate($value)
    {
        return true;
    }

    /**
     * @throws
     */
    public function addClass($class)
    {
        $this->classes[] = $class;
    }

    /**
     * @throws
     */
    public function removeClass($class)
    {
        for ($i = 0, $c = count($this->classes); $i < $c; $i++)
        {
            if ($this->classes[$i] === $class)
            {
                unset($this->classes[$i]);
            }
        }
    }

    /**
     * @throws
     */
    public function setClasses($classes = [])
    {
        $this->classes = $classes !== null ? $classes : [];
    }

    /**
     * @throws
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @throws
     */
    public function addStyle($style)
    {
        $this->styles[] = $style;
    }

    /**
     * @throws
     */
    public function removeStyle($style)
    {
        for ($i = 0, $c = count($this->styles); $i < $c; $i++)
        {
            if ($this->styles[$i] === $style)
            {
                unset($this->styles[$i]);
            }
        }
    }

    /**
     * @throws
     */
    public function setStyles($styles = [])
    {
        $this->styles = $styles !== null ? $styles : [];
    }

    /**
     * @throws
     */
    public function getStyles()
    {
        return $this->styles;
    }

    public function __toString()
    {
        try
        {
            $this->data->class = implode(' ', $this->classes);
            $this->data->style = implode('; ', $this->styles);

            foreach ($this->data as $key => $value)
            {
                if (is_array($value) || is_object($value))
                {
                    continue;
                }
                $this->contentView = str_replace('{$' . $key . '}', $value, $this->contentView);
            }
            return $this->contentView;
        }
        catch (Exception $e)
        {
            return $e->getMessage() . ': ' . $e->getTraceAsString();
        }
    }
}
