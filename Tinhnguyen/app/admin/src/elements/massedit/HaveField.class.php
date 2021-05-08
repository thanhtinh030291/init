<?php

namespace Lza\App\Admin\Elements\MassEdit;


use Lza\App\Admin\Elements\MassEditInput;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Form\MultipleSelection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class HaveField extends MultipleSelection
{
    use MassEditInput
    {
        onCreate as protected onMasseditCreate;
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
        $this->onMasseditCreate($metadata, $item);

        if (strpos($metadata['field'], "{$metadata['table']}_") !== false)
        {
            $this->refTable = str_replace("{$metadata['table']}_", '', $metadata['field']);
        }
        elseif (strpos($metadata['field'], "_{$metadata['table']}") !== false)
        {
            $this->refTable = str_replace("_{$metadata['table']}", '', $metadata['field']);
        }
        $refModule = chain_case($this->refTable);
        $this->data->addLink = WEBSITE_ROOT . $this->env->region . "/{$refModule}/add";

        $items = [];
        $model = ModelPool::getModel($this->refTable);
        $data = $model->select("id `id`, {$metadata['display']} `{$metadata['display']}`");
        foreach ($data as $item)
        {
            if ($this->validateInteger($metadata, $item['id']))
            {
                $items[$item['id']] = $item[$metadata['display']];
            }
        }

        $this->setItems($items);
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item = null)
    {
        $value = $this->form->isPost() && isset($this->request->{$this->data->name})
                ? $this->request->{$this->data->name} : '';
        $value = !$this->isRequired($metadata['mandatory']) && $value === '' ? null : $value;

        if ($this->advancedValidate($metadata, $value) && $this->form->isSubmitted())
        {
            $this->setValue($value);
        }
        elseif (!empty($item))
        {
            $model = ModelPool::getModel($metadata['field']);
            $joinItems = $model->where("{$metadata['table']}_id", $item['id']);

            $joinItemIds = [];
            foreach ($joinItems as $joinItem)
            {
                $joinItemIds[] = $joinItem["{$this->refTable}_id"];
            }
            $this->setValue($joinItemIds);
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
     * Retrieve the field label
     *
     * @throws
     */
    protected function getLabel($metadata, $checkRequired = true)
    {
        return $metadata["plural" . $this->session->lzalanguage];
    }
}
