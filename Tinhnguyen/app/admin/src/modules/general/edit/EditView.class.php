<?php

namespace Lza\App\Admin\Modules\General\Edit;


use Lza\App\Admin\Modules\AdminView;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Process Update page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class EditView extends AdminView
{
    use EditViewTrait;

    /**
     * Event when the page is creating
     *
     * @throws
     */
    protected function onCreateForm()
    {
        $this->data->editForm = DIContainer::resolve(
            EditForm::class, $this, "AdminEditForm"
        );

        $this->data->fields = $this->doGetTableFields($this->module);
        $this->data->editForm->setInputs($this->data->fields);

        $values = $this->data->editForm->getValues();
        $displayFields = explode(',', $this->data->table['display']);
        $displayLabels = [];
        foreach ($displayFields as $displayField)
        {
            $displayLabels[] = $values[$displayField] ?? null;
        }
        $this->data->itemLabel = $this->i18n->itemInformation(
            $this->data->table["single{$this->session->lzalanguage}"],
            implode(' - ', $displayLabels)
        );
    }

    /**
     * Event when CSSes is loading
     *
     * @throws
     */
    protected function onLoadStyles()
    {
        parent::onLoadStyles();
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'libraries/datetimepicker/DatetimePicker/jquery.datetimepicker.css';
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'vendors/select2/select2/dist/css/select2.min.css';
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'admin-res/styles/select2-bootstrap.min.css';
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'admin-res/styles/edit.css';
    }
}
