<?php

namespace Lza\App\Admin\Elements;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait MassEditInput
{
    use AdminInput;

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
     * @throws
     */
    protected function isRequired($mandatory)
    {
        return false;
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
    }

    /**
     * @throws
     */
    protected function validateMandatory($metadata, $value)
    {
        return true;
    }

    /**
     * @throws
     */
    protected function validateUnique($metadata, $value)
    {
        return false;
    }
}
