<?php

namespace Lza\App\Admin\Elements\Import;


use Lza\App\Admin\Elements\ImportInput;
use Lza\LazyAdmin\Form\CheckBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class CheckboxField extends CheckBox
{
    use ImportInput;

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item)
    {
        $value = $item[$metadata['index']] === 'Yes' ? 1 : 0;
        if ($this->advancedValidate($metadata, $value) && $this->form->isSubmitted())
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
        return $this->validateBoolean($metadata, $value);
    }
}
