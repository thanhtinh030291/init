<?php

namespace Lza\App\Admin\Elements\History;


use Lza\App\Admin\Elements\HistoryInput;
use Lza\LazyAdmin\Form\TextAreaBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ObjectField extends TextAreaBox
{
    use HistoryInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = null)
    {
        parent::__construct($form, $metadata['field']);
        $this->setPlaceHolder(ucwords(str_replace('_', ' ', $metadata['type'])));

        if (!empty($item))
        {
            $item[$this->data->name] = empty($item[$this->data->name]) || !$item[$this->data->name]
                    ? null : base64_encode($item[$this->data->name]);
        }
        $this->onCreate($metadata, $item);
    }
}
