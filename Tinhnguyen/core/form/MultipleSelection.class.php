<?php

namespace Lza\LazyAdmin\Form;


/**
 * Multiple Selection processes HTML Select Fields with option mutiple activated
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class MultipleSelection extends Selection
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <select multiple="multiple"
                id="{$id}" name="{$name}"
                class="{$class}" style="{$style}">
            {$options}
        </select>
    ';

    /**
     * @throws
     */
    public function __construct($form, $name, $items = [], $classes = [], $styles = [], $value = [])
    {
        parent::__construct($form, $name, $items, $classes, $styles, $value);
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
    protected function compareSelection($needle, $haystack)
    {
        return $haystack !== null
            && count($haystack) > 0
            && in_array($needle, $haystack);
    }

    /**
     * @throws
     */
    protected function validate($value)
    {
        return $value === null
            || is_array($value)
            || is_object($value);
    }
}
