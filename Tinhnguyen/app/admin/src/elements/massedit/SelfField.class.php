<?php

namespace Lza\App\Admin\Elements\MassEdit;


use Lza\App\Admin\Elements\MassEditInput;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Form\Selection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SelfField extends Selection
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
     * Event when the object is creating
     *
     * @throws
     */
    protected function onCreate($metadata, $item = null)
    {
        $this->onMasseditCreate($metadata, $item);

        $items = [
            '' => $this->i18n->get('None')
        ];
        $model = ModelPool::getModel($metadata['table']);
        $data = $model->select("id `id`, {$metadata['display']} `{$metadata['display']}`");
        foreach ($data as $item)
        {
            $items[$item['id']] = $item[$metadata['display']];
        }

        $this->setItems($items);
    }
}
