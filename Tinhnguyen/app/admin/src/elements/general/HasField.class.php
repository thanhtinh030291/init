<?php

namespace Lza\App\Admin\Elements\General;


use Lza\App\Admin\Elements\AdminInput;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Form\MultipleSelection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class HasField extends MultipleSelection
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
        parent::__construct($form, $metadata['field'], [], [], [], []);
        $this->onCreate($metadata, $item);
    }

    /**
     * Event when the field is creating
     *
     * @throws
     */
    protected function onCreate($metadata, $item = [])
    {
        $module = $this->env->module;

        $this->onAdminCreate($metadata, $item);

        $refModule = chain_case($metadata['field']);
        $this->data->addLink = WEBSITE_ROOT . "{$this->env->region}/{$refModule}/add";
        $this->data->showLink = WEBSITE_ROOT . "{$this->env->region}/{$refModule}/show";

        $items = [];
        $model = ModelPool::getModel("{$metadata['field']}");
        $data = $model->select("id `id`, {$metadata['display']} `{$metadata['display']}`");
        if ($metadata['form'] === 'show' && !empty($item))
        {
            $data = $data->where($metadata['table'] . '_id', $item['id']);
        }

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
        $module = $this->env->module;

        $value = $this->form->isPost() && isset($this->request->{$this->data->name})
                ? $this->request->{$this->data->name} : '';
        $value = !$this->isRequired($metadata['mandatory']) && $value === '' ? null : $value;

        if ($this->advancedValidate($metadata, $value) && $this->form->isSubmitted())
        {
            $this->setValue($value);
        }
        elseif (!empty($item))
        {
            $selfField = snake_case($module);

            $model = ModelPool::getModel("{$metadata['field']}");
            $joinItems = $model->where("{$selfField}_id", $item['id']);

            $joinItemIds = [];
            foreach ($joinItems as $joinItem)
            {
                $joinItemIds[] = $joinItem["{$selfField}_id"];
            }
            $this->setValue($joinItemIds);
        }
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
