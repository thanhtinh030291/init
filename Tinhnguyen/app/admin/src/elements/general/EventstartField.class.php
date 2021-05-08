<?php

namespace Lza\App\Admin\Elements\General;


use Lza\App\Admin\Elements\AdminInput;
use Lza\LazyAdmin\Form\TextBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class EventstartField extends TextBox
{
    use AdminInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = null)
    {
        parent::__construct($form, $metadata['field']);
        $this->setPlaceHolder(ucwords(str_replace('_', ' ', $metadata['type'])));

        $this->data->format = DATETIME_FORMAT;
        if (!empty($item))
        {
            $item[$this->data->name] = empty($item[$this->data->name]) || !$item[$this->data->name]
                    ? null : $item[$this->data->name]->format($this->data->format);
        }
        $this->onCreate($metadata, $item);
    }

    /**
     * Retreive the field value
     *
     * @throws
     */
    public function getValue()
    {
        return date_create_from_format($this->data->format, $this->data->value);
    }

    /**
     * Validate the field with the more advanced rules than the field itself
     *
     * @throws
     */
    protected function advancedValidate($metadata, $value)
    {
        return $this->validateMandatory($metadata, $value)
            && $this->validateUnique($metadata, $value)
            && $this->validateDateTime($metadata, $value);
    }
}
