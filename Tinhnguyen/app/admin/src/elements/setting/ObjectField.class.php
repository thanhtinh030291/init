<?php

namespace Lza\App\Admin\Elements\Setting;


use Lza\App\Admin\Elements\SettingInput;
use Lza\LazyAdmin\Form\TextAreaBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class TextareaField extends TextAreaBox
{
    use SettingInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata)
    {
        parent::__construct($form, $metadata['id']);
        $this->setPlaceHolder(ucwords(str_replace('_', ' ', $metadata['type'])));
        $metadata['value'] = $metadata['value'] === null || !$metadata['value']
                ? null : base64_encode($metadata['value']);
        $this->onCreate($metadata);
    }

    /**
     * Retreive the field value
     *
     * @throws
     */
    public function getValue()
    {
        return base64_decode($this->data->value);
    }
}
