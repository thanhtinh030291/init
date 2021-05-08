<?php

namespace Lza\App\Admin\Elements\General;


use Lza\App\Admin\Elements\AdminInput;
use Lza\LazyAdmin\Form\HiddenField;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class IdField extends HiddenField
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
}