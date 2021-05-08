<?php

namespace Lza\App\Admin\Elements\Setting;


use Lza\App\Admin\Elements\SettingInput;
use Lza\LazyAdmin\Form\Selection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class EnumField extends Selection
{
    use SettingInput
    {
        onCreate as protected onSettingCreate;
    }

    /**
     * @throws
     */
    public function __construct($form, $metadata)
    {
        parent::__construct($form, $metadata['id']);
        $this->onCreate($metadata);
    }

    /**
     * Event when the field is creating
     *
     * @throws
     */
    protected function onCreate($metadata, $item = null)
    {
        $this->onSettingCreate($metadata, $item = null);

        $items = $this->encryptor->jsonDecode($metadata['extra'], true);
        $this->setItems($items);
    }

    /**
     * @throws
     */
    public function setItems(array $items, $isAssoc = true)
    {
        $options = [];
        for ($i = 0, $c = count($items); $i < $c; $i++)
        {
            $value = $items[$i];
            $selected = '';
            if ($this->compareSelection($value, $this->data->value))
            {
                $selected = 'selected="selected"';
            }
            $key = $this->i18n->get($value);
            $options[] = "
                <option id=\"{$this->data->id}-{$i}\" value=\"{$value}\" {$selected}>
                    {$key}
                </option>
            ";
        }
        $this->data->options = implode('', $options);
    }
}
