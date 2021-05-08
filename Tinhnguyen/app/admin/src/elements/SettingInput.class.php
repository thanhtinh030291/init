<?php

namespace Lza\App\Admin\Elements;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait SettingInput
{
    use AdminInput;

    /**
     * Retrieve the field label
     *
     * @throws
     */
    protected function getLabel($metadata, $checkRequired = true)
    {
        return $metadata["title{$this->session->lzalanguage}"];
    }

    /**
     * Retrieve the field note
     *
     * @throws
     */
    protected function getNote($metadata)
    {
        return $metadata["title{$this->session->lzalanguage}"];
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
        elseif ($metadata['value'] !== null)
        {
            $this->setValue($metadata['value']);
        }
    }
}
