<?php

namespace Lza\App\Admin\Elements\Setting;


use Lza\App\Admin\Elements\SettingInput;
use Lza\LazyAdmin\Form\TextBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class DateField extends TextBox
{
    use SettingInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata)
    {
        parent::__construct($form, $metadata['id']);
        $this->setPlaceHolder(ucwords(str_replace('_', ' ', $metadata['type'])));
        $this->data->format = DATE_FORMAT;
        $metadata['value'] = $metadata['value'] === null || !$metadata['value']
                ? null : $metadata['value']->format($this->data->format);
        $this->onCreate($metadata);
    }

    /**
     * Retreive the field value
     *
     * @throws
     */
    public function getValue()
    {
        return date_create_from_format($this->data->format, $this->data->value);
    }

    /**
     * Validate the field with the more advanced rules than the field itself
     *
     * @throws
     */
    protected function advancedValidate($metadata, $value)
    {
        return $this->validateDate($metadata, $value);
    }
}
