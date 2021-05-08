<?php

namespace Lza\App\Admin\Elements\General;


use Lza\App\Admin\Elements\AdminInput;
use Lza\LazyAdmin\Form\TextAreaBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ObjectField extends TextAreaBox
{
    use AdminInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = null)
    {
        parent::__construct($form, $metadata['field']);
        $this->setPlaceHolder(ucwords(str_replace('_', ' ', $metadata['type'])));
        list($filename, $filetype) = explode(':', $metadata['display']);
        if (!empty($item))
        {
            $this->data->filetype = $item[$filetype];
            $item[$this->data->name] = empty($item[$this->data->name]) || !$item[$this->data->name]
                    ? null : base64_encode($item[$this->data->name]);
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
        return base64_decode($this->data->value);
    }

    /**
     * Validate the field with the more advanced rules than the field itself
     *
     * @throws
     */
    protected function advancedValidate($metadata, $value)
    {
        return $this->validateMandatory($metadata, $value)
            && $this->validateUnique($metadata, $value);
    }
}
