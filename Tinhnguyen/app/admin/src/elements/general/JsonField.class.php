<?php

namespace Lza\App\Admin\Elements\General;


use Lza\App\Admin\Elements\AdminInput;
use Lza\LazyAdmin\Form\TextBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class JsonField extends TextBox
{
    use AdminInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = null)
    {
        parent::__construct($form, $metadata['field']);
        $this->setPlaceHolder(ucwords(str_replace('_', ' ', $metadata['type'])));
        $this->onCreate($metadata, $item);
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item = null)
    {
        $value = $this->form->isPost() && isset($_POST[$this->data->name]) ? $_POST[$this->data->name] : '';
        $value = !$this->isRequired($metadata['mandatory']) && $value === '' ? null : $value;

        if ($this->advancedValidate($metadata, $value) && $this->form->isSubmitted())
        {
            $this->setValue($value);
        }
        elseif (!empty($item))
        {
            $this->setValue($this->encryptor->jsonEncode($item[$this->data->name]));
        }
    }

    /**
     * Retreive the field value
     *
     * @throws
     */
    public function getValue()
    {
        return $this->data->value !== null ? $this->encryptor->jsonDecode($this->data->value) : null;
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
            && $this->validateJson($metadata, $value);
    }

    /**
     * Retrieve the client side script of the field
     *
     * @throws
     */
    public function getContentScript()
    {
        return strlen($this->data->value) === 0 ? '' : parent::getContentScript();
    }
}
