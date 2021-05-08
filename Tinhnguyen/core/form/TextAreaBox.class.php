<?php

namespace Lza\LazyAdmin\Form;


/**
 * Text Area processes HTML Text Area Fields
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class TextAreaBox extends TextBox
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <textarea id="{$id}" name="{$name}"
                  class="{$class}" style="{$style}"
                  placeholder="{$placeholder}"
                  rows="{$rows}" cols="{$cols}">{$value}</textarea>
    ';

    /**
     * @var integer Number of rows
     */
    protected $rows;

    /**
     * @var integer Number of columns
     */
    protected $cols;

    /**
     * @throws
     */
    public function __construct(
        $form, $name, $classes = [], $styles = [], $value = null,
        $placeholder = '', $rows = null, $cols = null
    )
    {
        parent::__construct($form, $name, $classes, $styles, $value, $placeholder);
        $this->setRows($rows);
        $this->setCols($cols);
    }

    /**
     * @throws
     */
    public function setRows($rows)
    {
        $this->data->rows = $rows;
    }

    /**
     * @throws
     */
    public function getRows()
    {
        return $this->data->rows;
    }

    /**
     * @throws
     */
    public function setCols($cols)
    {
        $this->data->cols = $cols;
    }

    /**
     * @throws
     */
    public function getCols()
    {
        return $this->data->cols;
    }
}
