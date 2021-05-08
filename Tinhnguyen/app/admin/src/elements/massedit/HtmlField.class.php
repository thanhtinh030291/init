<?php

namespace Lza\App\Admin\Elements\MassEdit;


use Lza\App\Admin\Elements\MassEditInput;
use Lza\LazyAdmin\Form\TextAreaBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class HtmlField extends TextAreaBox
{
    use MassEditInput;

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
        if (!$this->form->isSubmitted() && !empty($item))
        {
            return;
        }
        else
        {
            $value = $this->form->isPost() && isset($_POST[$this->data->name])
                    ? $_POST[$this->data->name] : '';
            $value = !$this->isRequired($metadata['mandatory']) && $value === '' ? null : $value;
        }
        if ($this->advancedValidate($metadata, $value))
        {
            $this->setValue($value);
        }
    }
}
