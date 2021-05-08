<?php

namespace Lza\LazyAdmin\Form;


use Lza\LazyAdmin\Form\Selection;

/**
 * Radio Group processes Radio Buttons Groups
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class RadioGroup extends Selection
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '{$options}';

    /**
     * @throws
     */
    public function setItems(array $items)
    {
        if (array_is_assoc($items))
        {
            return false;
        }

        $this->data->class = implode(' ', $this->classes);
        $this->data->style = implode('; ', $this->styles);

        $options = [];
        for ($i = 0, $c = count($items); $i < $c; $i++)
        {
            $value = $items[$i];

            $checked = '';
            if ($value === $this->data->value)
            {
                $checked = 'checked="checked"';
            }

            echo "
                <input type=\"radio\"
                       id=\"{$this->data->id}-{$i}\"
                       name=\"{$this->data->name}\"
                       class=\"{$this->data->class}\"
                       style=\"{$this->data->style}\"
                       {$checked} />
                {$this->data->value}
            ";
        }

        $this->data->options = implode('', $options);
    }
}
