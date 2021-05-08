<?php

namespace Lza\App\Admin\Modules\General\Add;


use Lza\App\Admin\Modules\AdminView;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Process Add New page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class AddView extends AdminView
{
    use AddViewTrait;

    /**
     * Event when the page is creating
     *
     * @throws
     */
    protected function onCreateForm()
    {
        $this->data->addForm = DIContainer::resolve(
            AddForm::class, $this, "AdminAddForm"
        );

        $this->data->fields = $this->doGetTableFields($this->module);
        $this->data->addForm->setInputs($this->data->fields);
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
                . 'admin-res/styles/add.css';
    }
}
