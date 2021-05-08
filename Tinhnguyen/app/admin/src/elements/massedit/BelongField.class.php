<?php

namespace Lza\App\Admin\Elements\MassEdit;


use Lza\App\Admin\Elements\MassEditInput;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Form\Selection;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class BelongField extends Selection
{
    use MasseditInput
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

        $this->data->addLink = WEBSITE_ROOT . $this->env->region . "/"
                . str_replace('_', '-', $metadata['field']) . "/add";
        if (!empty($item))
        {
            $this->data->showLink = WEBSITE_ROOT . $this->env->region . "/"
                    . str_replace('_', '-', $metadata['field'])
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
        $value = $this->form->isPost() && isset($this->request->{$this->data->name})
                ? $this->request->{$this->data->name} : '';
        $value = !$this->isRequired($metadata['mandatory']) && $value === '' ? null : $value;

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
}
