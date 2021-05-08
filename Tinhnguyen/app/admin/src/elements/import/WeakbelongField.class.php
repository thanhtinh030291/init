<?php

namespace Lza\App\Admin\Elements\Import;


use Lza\App\Admin\Elements\ImportInput;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Form\Selection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class WeakbelongField extends Selection
{
    use ImportInput;

    /**
     * @var string Database Table which has n-1 relationship to current table by this field
     */
    protected $reference;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item)
    {
        $fieldParts = explode(':', $metadata['field']);
        $this->reference = $fieldParts[1];

        parent::__construct($form, $metadata['field']);
        $this->data->value = null;
        $this->onCreate($metadata, $item);
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item)
    {
        $value = $item[$metadata['index']];
        $fieldParts = explode(':', $metadata['field']);
        $refTable = $fieldParts[0];
        $refDisplay = $metadata['display'];
        $model = ModelPool::getModel($refTable);
        $refItem = $model->where("{$refTable}.{$refDisplay}", $value);
        $refItem = $refItem->fetch();
        $value = !$refItem ? null : $refItem['id'];
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
        $this->form->setValue("{$this->data->name}_id", $this->getValue());
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
