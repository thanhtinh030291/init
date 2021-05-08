<?php

namespace Lza\App\Admin\Elements\MassEdit;


use Lza\App\Admin\Elements\MassEditInput;
use Lza\LazyAdmin\Form\HiddenField;

class IdField extends HiddenField
{
    use MassEditInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = null)
    {
        parent::__construct($form, $metadata['field']);
        $this->onCreate($metadata, $item);
    }
}