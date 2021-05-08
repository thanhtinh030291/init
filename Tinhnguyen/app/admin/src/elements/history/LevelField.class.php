<?php

namespace Lza\App\Admin\Elements\History;


use Lza\App\Admin\Elements\HistoryInput;
use Lza\LazyAdmin\Form\MultipleSelection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LevelField extends MultipleSelection
{
    use HistoryInput
    {
        onCreate as protected onHistoryCreate;
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
        $this->onHistoryCreate($metadata, $item);

        $items = [];
        $options = $this->encryptor->jsonDecode($metadata['display'], true);
        foreach ($options as $option => $level)
        {
            $items[$this->i18n->get($option)] = $level;
        }
        $this->setItems($items);
    }

    /**
     * @throws
     */
    protected function compareSelection($needle, $haystack)
    {
        return intval(intval($needle) & intval($haystack)) === intval($needle);
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
            $this->setValue($item[$this->data->name]);
        }
    }
}
