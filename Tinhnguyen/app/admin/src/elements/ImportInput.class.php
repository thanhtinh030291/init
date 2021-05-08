<?php

namespace Lza\App\Admin\Elements;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait ImportInput
{
    use AdminValidator;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item)
    {
        parent::__construct($form, $metadata['field']);
        $this->data->value = null;
        $this->onCreate($metadata, $item);
    }

    /**
     * Event when the field is creating
     *
     * @throws
     */
    protected function onCreate($metadata, $item)
    {
        $this->data->errors = [];
        $this->onSetValue($metadata, $item);
        $this->data->errors = implode('<br />', $this->data->errors);
        $this->onSetFormValue();
    }

    /**
     * Retrieve the field label
     *
     * @throws
     */
    protected function getLabel($metadata, $checkRequired = true)
    {
        return $metadata["single" . $this->session->lzalanguage];
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item)
    {
        $value = $item[$metadata['index']];
        $value = !$this->isRequired($metadata['mandatory']) && $value === '' ? null : $value;

        if ($this->advancedValidate($metadata, $value) && $this->form->isSubmitted() && $value !== '')
        {
            $this->setValue($value);
        }
    }

    /**
     * Event when the field value is pass to it's form
     *
     * @throws
     */
    protected function onSetFormValue()
    {
        $this->form->setValue($this->data->name, $this->getValue());
    }
}
