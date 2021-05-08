<?php

namespace Lza\App\Admin\Elements\Import;


use Lza\App\Admin\Elements\ImportInput;
use Lza\LazyAdmin\Form\TextBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LinkField extends TextBox
{
    use ImportInput;

    /**
     * Validate the field with the more advanced rules than the field itself
     *
     * @throws
     */
    protected function advancedValidate($metadata, $value)
    {
        return $this->validateMandatory($metadata, $value)
            && $this->validateUnique($metadata, $value)
            && $this->validateLink($metadata, $value);
    }
}
