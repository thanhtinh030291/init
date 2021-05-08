<?php

namespace Lza\App\Admin\Elements\History;


use Lza\App\Admin\Elements\HistoryInput;
use Lza\LazyAdmin\Form\NumberBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SequenceField extends NumberBox
{
    use HistoryInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = null)
    {
        parent::__construct($form, $metadata['field']);
        $this->setPlaceHolder(ucwords(str_replace('_', ' ', $metadata['type'])));
        $this->onCreate($metadata, $item);
    }
}
