<?php

namespace Lza\App\Admin\Elements\MassEdit;


use Lza\App\Admin\Elements\MassEditInput;
use Lza\LazyAdmin\Form\Selection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class EnumField extends Selection
{
    use MassEditInput
    {
        onCreate as protected onMasseditCreate;
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
        $this->onMasseditCreate($metadata, $item);

        $items = $this->encryptor->jsonDecode($metadata['display'], true);
        $this->setItems($items);
    }
}
