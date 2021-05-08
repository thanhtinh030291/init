<?php

namespace Lza\App\Admin\Elements\Import;


use Lza\App\Admin\Elements\ImportInput;
use Lza\LazyAdmin\Form\TextAreaBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ObjectField extends TextAreaBox
{
    use ImportInput;

    /**
     * Retreive the field value
     *
     * @throws
     */
    public function getValue()
    {
        return base64_decode($this->data->value);
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
