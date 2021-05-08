<?php

namespace Lza\App\Admin\Elements\MassEdit;


use Lza\App\Admin\Elements\MassEditInput;
use Lza\LazyAdmin\Form\MultipleSelection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LevelField extends MultipleSelection
{
    use MassEditInput
    {
        onCreate as protected onMasseditCreate;
    }

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = [])
    {
        parent::__construct($form, $metadata['field'], [], [], [], 0);
        $this->onCreate($metadata, $item);
    }

    /**
     * Event when the field is creating
     *
     * @throws
     */
    protected function onCreate($metadata, $item = null)
    {
        $this->onMasseditCreate($metadata, $item);

        $items = [];
        $options = $this->encryptor->jsonDecode($metadata['display'], true);
        foreach ($options as $option => $level)
        {
            $items[$level] = $this->i18n->get($option);
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
        $value = $this->form->isPost() && isset($this->request->{$this->data->name})
                ? $this->request->{$this->data->name} : [0];
        $level = array_sum($value);

        if ($this->advancedValidate($metadata, $level))
        {
            $this->setValue($level);
        }
    }

    /**
     * @throws
     */
    protected function validate($value)
    {
        return $this->validator->validateInteger($value);
    }
}
