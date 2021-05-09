<?php

namespace Lza\App\Admin\Elements\General;


use Lza\App\Admin\Elements\AdminInput;
use Lza\LazyAdmin\Form\NumberBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class DoubleField extends NumberBox
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
     * Validate the field with the more advanced rules than the field itself
     *
     * @throws
     */
    protected function advancedValidate($metadata, $value)
    {
        return $this->validateMandatory($metadata, $value)
            && $this->validateUnique($metadata, $value)
            && $this->validateNumber($metadata, $value)
            && $this->validateMinValue($metadata, $value)
            && $this->validateMaxValue($metadata, $value);
    }
}