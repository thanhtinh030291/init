<?php

namespace Lza\App\Admin\Modules\General\Show;


use Lza\App\Admin\Modules\AdminView;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Process Details page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ShowView extends AdminView
{
    use ShowViewTrait;

    /**
     * Event when the page is creating
     *
     * @throws
     */
    protected function onCreateForm()
    {
        $this->data->showForm = DIContainer::resolve(
            ShowForm::class, $this, "AdminShowForm"
        );

        $this->data->fields = $this->doGetTableFields($this->module);
        $this->data->showForm->setInputs($this->data->fields);

        $values = $this->data->showForm->getValues();
        $displayFields = explode(',', $this->data->table['display']);
        $displayLabels = [];
        if (!empty($value))
        {
            foreach ($displayFields as $displayField)
            {
                $displayLabels[] = $values[$displayField];
            }
            $this->data->itemExists = true;
        }
        $this->data->itemLabel = $this->i18n->itemInformation(
            $this->data->table["single{$this->session->lzalanguage}"],
            implode(' - ', $displayLabels)
        );

        $settings = htmlspecialchars_decode($this->data->table['settings']);
        $this->data->options = empty($settings) ? [] : $this->encryptor->jsonDecode($settings, true);
        if (isset($this->data->options['history']) && $this->data->options['history'] !== false)
        {
            $this->data->versionsForm = DIContainer::resolve(
                VersionsForm::class, $this,
                "AdminVersionsForm", $this->data->itemLabel
            );
            $this->data->versionsForm->setInputs($this->data->fields);
        }
    }
}
