<?php

namespace Lza\App\Admin\Elements\History;


use Lza\App\Admin\Elements\HistoryInput;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Form\Selection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class BelongField extends Selection
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

        $this->data->addLink = WEBSITE_ROOT . $this->env->region . "/"
                . str_replace('_', '-', $metadata['field']) . "/add";
        if (!empty($item))
        {
            $this->data->showLink = WEBSITE_ROOT . $this->env->region
                    . "/" . str_replace('_', '-', $metadata['field'])
                    . "/show/" . $item["{$metadata['field']}_id"];
        }

        $items = [
            '' => $this->i18n->get('None')
        ];
        $model = ModelPool::getModel($metadata['field']);
        $data = $model->select("id `id`, {$metadata['display']} `{$metadata['display']}`");
        foreach ($data as $item)
        {
            $items[$item['id']] = $item[$metadata['display']];
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
        if (!empty($item))
        {
            $this->setValue($item["{$this->data->name}_id"]);
        }
    }
}
