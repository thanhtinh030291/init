<?php

namespace Lza\App\Admin\Elements\History;


use Lza\App\Admin\Elements\HistoryInput;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Form\Selection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class WeakbelongField extends Selection
{
    use HistoryInput
    {
        onCreate as protected onHistoryCreate;
    }

    /**
     * @var string Database Table which has n-1 relationship to current table by this field
     */
    protected $reference;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = null)
    {
        $fieldParts = explode(':', $metadata['field']);
        $this->reference = $this->fieldParts[1];

        parent::__construct($form, $this->fieldParts[0]);
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

        $this->data->addLink = WEBSITE_ROOT . $this->env->region . "/" . str_replace('_', '-', $this->reference) . "/add";

        $items = [
            '' => $this->i18n->get('None')
        ];
        $model = ModelPool::getModel($this->data->name);
        $data = $model->select("id `id`, {$metadata['display']} `{$metadata['display']}`");
        foreach ($data as $item)
        {
            $items[$item['id']] = $item[$metadata['display']];
        }

        $this->setItems($items);
    }
}
