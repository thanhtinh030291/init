<?php

namespace Lza\App\Admin\Elements\MassEdit;


use Lza\App\Admin\Elements\MassEditInput;
use Lza\LazyAdmin\Form\MultipleSelection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class EnumsField extends MultipleSelection
{
    use MassEditInput
    {
        onCreate as protected onMasseditCreate;
    }

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = [])
    {
        parent::__construct($form, $metadata['field']);
        $this->onCreate($metadata, $item);
    }

    /**
     * Event when the field is creating
     *
     * @throws
     */
    protected function onCreate($metadata, $item = [])
    {
        $this->onMasseditCreate($metadata, $item);

        $items = $this->encryptor->jsonDecode($metadata['display'], true);
        $this->setItems($items);
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item = [])
    {
        $values = $this->form->isPost() && isset($this->request->{$this->data->name})
                ? $this->request->{$this->data->name} : [];
        $values = !$this->isRequired($metadata['mandatory']) && count($values) === 0 ? null : $values;

        if ($this->advancedValidate($metadata, $values) && $this->form->isSubmitted())
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
}
