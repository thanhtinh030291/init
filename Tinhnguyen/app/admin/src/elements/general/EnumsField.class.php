<?php

namespace Lza\App\Admin\Elements\General;


use Lza\App\Admin\Elements\AdminInput;
use Lza\LazyAdmin\Form\MultipleSelection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class EnumsField extends MultipleSelection
{
    use AdminInput
    {
        onCreate as protected onAdminCreate;
    }

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = null)
    {
        parent::__construct($form, $metadata['field']);
        $this->onCreate($metadata, $item);
    }

    /**
     * Event when the field is creating
     *
     * @throws
     */
    protected function onCreate($metadata, $item = null)
    {
        $this->onAdminCreate($metadata, $item);

        $items = $this->encryptor->jsonDecode($metadata['display'], true);
        $this->setItems($items, false);
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item = null)
    {
        if (!$this->form->isSubmitted() && !empty($item))
        {
            $values = $item[$this->data->name];
        }
        else
        {
            $values = $this->form->isPost() && isset($this->request->{$this->data->name})
                    ? $this->request->{$this->data->name} : [];
            $values = !$this->isRequired($metadata['mandatory']) && count($values) === 0 ? null : $values;

        }
        if ($this->advancedValidate($metadata, $values) && $this->form->isSubmitted())
        {
            $this->setValue($values);
        }
        elseif (!empty($item))
        {
            $this->setValue($values);
        }
    }

    /**
     * Retrieve the field label
     *
     * @throws
     */
    protected function getLabel($metadata, $checkRequired = true)
    {
        return $metadata["plural" . $this->session->lzalanguage];
    }

    /**
     * Validate the field with the more advanced rules than the field itself
     *
     * @throws
     */
    protected function advancedValidate($metadata, $value)
    {
        return $this->validateMandatory($metadata, $value);
    }
}
