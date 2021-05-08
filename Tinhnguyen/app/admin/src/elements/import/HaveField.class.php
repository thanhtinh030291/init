<?php

namespace Lza\App\Admin\Elements\Import;


use Lza\App\Admin\Elements\ImportInput;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Form\MultipleSelection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class HaveField extends MultipleSelection
{
    use ImportInput;

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item)
    {
        $values = explode("\n", $item[$metadata['index']]);
        $refTable = $metadata['field'];
        $refDisplayValue = $metadata['display'];
        $refDisplayTable = trim(str_replace($metadata['table'], '', $metadata['field']), '_');
        $refDisplayField = "{$refDisplayTable}_id";
        $model = ModelPool::getModel($refDisplayTable);
        $refItems = $model->where("{$refDisplayTable}.{$refDisplayValue}", $values);
        $refItems->select("id");
        $refItemIds = [];
        foreach ($refItems as $refItem)
        {
            $refItemIds[] = $refItem['id'];
        }
        $value = $refItemIds;
        if ($this->advancedValidate($metadata, $value) && $this->form->isSubmitted())
        {
            $this->setValue($value);
        }
    }

    /**
     * Event when the field value is pass to it's form
     *
     * @throws
     */
    protected function onSetFormValue()
    {
        $this->form->setManyToManyValue($this->data->name, $this->getValue());
    }

    /**
     * Validate the field with the more advanced rules than the field itself
     *
     * @throws
     */
    protected function advancedValidate($metadata, $value)
    {
        return $this->validateMandatory($metadata, $value);
    }
}
