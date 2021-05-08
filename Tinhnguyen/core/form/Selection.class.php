<?php

namespace Lza\LazyAdmin\Form;


/**
 * Selection processes HTML Dropdown Lists
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Selection extends Input
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <select id="{$id}" name="{$name}"
                class="{$class}" style="{$style}">
            {$options}
        </select>
    ';

    /**
     * @var array Selection's items
     */
    protected $items;

    /**
     * @throws
     */
    public function __construct($form, $name, $items = [], $classes = [], $styles = [], $value = null)
    {
        parent::__construct($form, $name, $classes, $styles, $value);
        $this->setItems($items);
    }

    /**
     * @throws
     */
    public function setItems(array $items, $isAssoc = true)
    {
        $options = [];
        if ($isAssoc)
        {
            $i = 0;
            foreach ($items as $key => $value)
            {
                $selected = $this->compareSelection($key, $this->data->value) ? 'selected="selected"' : '';
                $options[] = "
                    <option id=\"{$this->data->id}-{$i}\"
                            value=\"{$key}\" {$selected}>
                        {$value}
                    </option>
                ";
                $i++;
            }
        }
        else
        {
            for ($i = 0, $c = count($items); $i < $c; $i++)
            {
                $value = $items[$i];
                $selected = $this->compareSelection($value, $this->data->value) ? 'selected="selected"' : '';
                $options[] = "
                    <option id=\"{$this->data->id}-{$i}\"
                            value=\"{$value}\" {$selected}>
                        {$value}
                    </option>
                ";
            }
        }
        $this->data->options = implode('', $options);
    }

    /**
     * @throws
     */
    protected function compareSelection($option, $value)
    {
        return strcmp($option, $value) === 0;
    }

    /**
     * @throws
     */
    public function getItems()
    {
        return $this->data->items;
    }

    /**
     * @throws
     */
    protected function validate($value)
    {
        return !(is_array($value) || is_object($value));
    }
}
