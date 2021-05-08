<?php

namespace Lza\App\Admin\Elements\Import;


use Lza\App\Admin\Elements\ImportInput;
use Lza\LazyAdmin\Form\TextBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class DateField extends TextBox
{
    use ImportInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item)
    {
        parent::__construct($form, $metadata['field']);
        $this->data->format = DATE_FORMAT;
        $this->onCreate($metadata, $item);
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item)
    {
        $value = isset($item[$metadata['index']]) ? $item[$metadata['index']] : '';
        $value = !$this->isRequired($metadata['mandatory']) && $value === '' ? null : $value;

        if ($this->advancedValidate($metadata, $value) && $this->form->isSubmitted())
        {
            $this->setValue($value);
        }
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
        return $this->validateMandatory($metadata, $value)
            && $this->validateUnique($metadata, $value)
            && $this->validateDate($metadata, $value);
    }
}
