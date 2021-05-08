<?php

namespace Lza\App\Admin\Elements\MassEdit;


use Lza\App\Admin\Elements\MassEditInput;
use Lza\LazyAdmin\Form\PhoneBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PhoneField extends PhoneBox
{
    use MassEditInput;

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
        return $this->validateRegex($metadata, $value)
            && $this->validateMinLength($metadata, $value)
            && $this->validateMaxLength($metadata, $value);
    }
}
