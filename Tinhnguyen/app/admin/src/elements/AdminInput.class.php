<?php

namespace Lza\App\Admin\Elements;


/**
 * @var request
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait AdminInput
{
    use AdminValidator;

    /**
     * Event when the field is creating
     *
     * @throws
     */
    protected function onCreate($metadata, $item = null)
    {
        $this->data->label = $this->getLabel($metadata);
        $this->data->note = $this->getNote($metadata);

        $this->data->errors = [];
        $this->onSetValue($metadata, $item);
        $this->data->errors = implode('<br />', $this->data->errors);

        if (!isset($metadata['set_form_value']) || $metadata['set_form_value'])
        {
            $this->onSetFormValue();
        }

        $this->addClass('form-control');
        $this->setContentView(
            $this->form->getLayoutPath() . "{$metadata['field_folder']}/{$metadata['type']}.html"
        );
        $this->setContentScript(
            $this->form->getScriptPath() . "{$metadata['field_folder']}/{$metadata['type']}.js"
        );
    }

    /**
     * Retrieve the field label
     *
     * @throws
     */
    protected function getLabel($metadata, $checkRequired = true)
    {
        $label = $metadata["single" . $this->session->lzalanguage];
        if ($checkRequired && $this->isRequired($metadata['mandatory']))
        {
            $label .= '<span class="text-danger">*</span>';
        }
        return $label;
    }

    /**
     * Retrieve the field note
     *
     * @throws
     */
    protected function getNote($metadata)
    {
        return $metadata['field_note'];
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item = null)
    {
        $value = $this->form->isPost() && isset($this->request->{$this->data->name})
                ? $this->request->{$this->data->name} : '';
        $value = !$this->isRequired($metadata['mandatory']) && $value === '' ? null : $value;

        if ($this->advancedValidate($metadata, $value) && $this->form->isSubmitted())
        {
            $this->setValue($value);
        }
        elseif (!empty($item))
        {
            $this->setValue($item[$this->data->name]);
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
