<?php

namespace Lza\App\Admin\Elements\General;


use Lza\App\Admin\Elements\AdminInput;
use Lza\LazyAdmin\Form\Selection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class EnumField extends Selection
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
