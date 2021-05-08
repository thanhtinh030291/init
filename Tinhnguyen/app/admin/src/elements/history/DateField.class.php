<?php

namespace Lza\App\Admin\Elements\History;


use Lza\App\Admin\Elements\HistoryInput;
use Lza\LazyAdmin\Form\TextBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class DateField extends TextBox
{
    use HistoryInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = null)
    {
        parent::__construct($form, $metadata['field']);
        $this->setPlaceHolder(ucwords(str_replace('_', ' ', $metadata['type'])));

        $this->data->format = DATE_FORMAT;
        if (!empty($item))
        {
            $item[$this->data->name] = empty($item[$this->data->name]) || !$item[$this->data->name]
                    ? null : $item[$this->data->name]->format($this->data->format);
        }
        $this->onCreate($metadata, $item);
    }
}
