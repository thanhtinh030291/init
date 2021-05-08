<?php

namespace Lza\App\Admin\Elements\Setting;


use Lza\App\Admin\Elements\SettingInput;
use Lza\LazyAdmin\Form\TextAreaBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class HtmlField extends TextAreaBox
{
    use SettingInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata)
    {
        parent::__construct($form, $metadata['id']);
        $this->setPlaceHolder(ucwords(str_replace('_', ' ', $metadata['type'])));
        $this->onCreate($metadata);
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item = null)
    {
        if (!$this->form->isSubmitted() && $metadata !== null)
        {
            $value = $metadata['value'];
        }
        else
        {
            $value = $this->form->isPost() && isset($_POST[$this->data->name])
                    ? $_POST[$this->data->name] : '';
            $value = !$this->isRequired($metadata['mandatory']) && $value === '' ? null : $value;
        }
        if ($this->advancedValidate($metadata, $value))
        {
            $this->setValue($value);
        }
    }
}
