<?php

namespace Lza\App\Admin\Elements\General;


use Lza\App\Admin\Elements\AdminInput;
use Lza\LazyAdmin\Form\TextAreaBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class HtmlField extends TextAreaBox
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
        if (!$this->form->isSubmitted() && !empty($item))
        {
            $value = $item[$this->data->name];
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

    /**
     * Validate the field with the more advanced rules than the field itself
     *
     * @throws
     */
    protected function advancedValidate($metadata, $value)
    {
        return $this->validateMandatory($metadata, $value)
            && $this->validateUnique($metadata, $value);
    }
}
