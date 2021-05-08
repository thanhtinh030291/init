<?php

namespace Lza\App\Admin\Elements\General;


use Lza\App\Admin\Elements\AdminInput;
use Lza\LazyAdmin\Form\CheckBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class CheckboxField extends CheckBox
{
    use AdminInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = null)
    {
        parent::__construct($form, $metadata['field']);
        $this->onCreate($metadata, $item);
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item = null)
    {
        $value = $this->form->isPost() && isset($this->request->{$this->data->name})
                ? ($this->request->{$this->data->name} === 'on' ? 1 : 0) : 0;
        if ($this->advancedValidate($metadata, $value) && $this->form->isSubmitted())
        {
            $this->setValue($value);
            $this->setChecked($value);
        }
        elseif (!empty($item))
        {
            $this->setValue($item[$this->data->name]);
            $this->setChecked($item[$this->data->name] === 1);
        }
    }

    /**
     * Validate the field with the more advanced rules than the field itself
     *
     * @throws
     */
    protected function advancedValidate($metadata, $value)
    {
        return $this->validateBoolean($metadata, $value);
    }
}
