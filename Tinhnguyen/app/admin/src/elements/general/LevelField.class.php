<?php

namespace Lza\App\Admin\Elements\General;


use Lza\App\Admin\Elements\AdminInput;
use Lza\LazyAdmin\Form\MultipleSelection;

/**
 * @var request
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LevelField extends MultipleSelection
{
    use AdminInput
    {
        onCreate as protected onAdminCreate;
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
        $this->onAdminCreate($metadata, $item);

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
    public function setItems(array $items, $isAssoc = true)
    {
        $options = [];
        $i = 0;
        foreach ($items as $key => $value)
        {
            $selected = $this->compareSelection($key, $this->data->value) ? 'selected="selected"' : '';
            $options[] = "
                <option id=\"{$this->data->id}-{$i}\"
                            value=\"{$key}\" {$selected}>
                    {$value}
                </option>
            ";
            $i++;
        }
        $this->data->options = implode('', $options);
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
        $level = 0;
        if (!$this->form->isSubmitted() && !empty($item))
        {
            $level = $item[$this->data->name];
        }
        else
        {
            $value = $this->form->isPost() && isset($this->request->{$this->data->name})
                    ? $this->request->{$this->data->name} : [0];
            $level = array_sum($value);
        }

        if ($this->advancedValidate($metadata, $level))
        {
            $this->setValue($level);
        }
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

    /**
     * @throws
     */
    protected function validate($value)
    {
        return $this->validator->validateInteger($value);
    }
}
