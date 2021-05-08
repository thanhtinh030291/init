<?php

namespace Lza\App\Admin\Modules\Setting\Edit;


use Lza\App\Admin\Modules\AdminView;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Process Setting page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SettingEditView extends AdminView
{
    /**
     * Event when the page is creating
     *
     * @throws
     */
    protected function onCreate()
    {
        parent::onCreate();

        if (!$this->isAjax())
        {
            $this->onCreateForm();
        }
    }

    /**
     * Event when the form is creating
     *
     * @throws
     */
    protected function onCreateForm()
    {
        $this->data->editForm = DIContainer::resolve(SettingEditForm::class, $this, "SettingEditForm");

        $this->data->fields = $this->doGetFields($this->view);
        $this->data->editForm->setInputs($this->data->fields);
    }

    /**
     * Event when CSSes is loading
     *
     * @throws
     */
    protected function onLoadStyles()
    {
        parent::onLoadStyles();
        $styles = [
            'libraries/datetimepicker/DatetimePicker/jquery.datetimepicker.css',
            'vendors/select2/select2/dist/css/select2.min.css',
            'admin-res/styles/select2-bootstrap.min.css',
            'admin-res/styles/edit.css'
        ];
        foreach ($styles as $style)
        {
            $this->data->styles['body'][] = WEBSITE_ROOT . $style;
        }
    }

    /**
     * Event when Javascript is loading
     *
     * @throws
     */
    protected function onLoadScripts()
    {
        parent::onLoadScripts();
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'vendors/select2/select2/dist/js/select2.full.min.js';
    }

    /**
     * Event when Update button is clicked
     *
     * @throws
     */
    public function onBtnUpdateClick()
    {
        $this->doSave($this->data->editForm->getValues());
    }
}
