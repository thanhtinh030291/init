<?php

namespace Lza\App\Admin\Elements\History;


use Lza\App\Admin\Elements\HistoryInput;
use Lza\LazyAdmin\Form\TextBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class JsonField extends TextBox
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

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item = null)
    {
        if (!empty($item))
        {
            $this->setValue($this->encryptor->jsonEncode($item[$this->data->name]));
        }
    }

    /**
     * Retrieve the client side script of the field
     *
     * @throws
     */
    public function getContentScript()
    {
        return strlen($this->data->value) === 0 ? '' : parent::getContentScript();
    }
}
