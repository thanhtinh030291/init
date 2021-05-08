<?php

namespace Lza\App\Admin\Elements\History;


use Lza\App\Admin\Elements\HistoryInput;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Form\MultipleSelection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class HaveField extends MultipleSelection
{
    use HistoryInput
    {
        onCreate as protected onHistoryCreate;
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
        $this->onHistoryCreate($metadata, $item);

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
        $model = ModelPool::getModel($metadata['field']);
        if (!empty($item))
        {
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
     * Retrieve the field label
     *
     * @throws
     */
    protected function getLabel($metadata, $checkRequired = true)
    {
        return $metadata["plural" . $this->session->lzalanguage];
    }
}
