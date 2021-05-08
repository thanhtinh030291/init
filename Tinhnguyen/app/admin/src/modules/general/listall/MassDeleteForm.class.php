<?php

namespace Lza\App\Admin\Modules\General\Listall;


use Lza\App\Admin\Elements\AdminForm;
use Lza\LazyAdmin\Form\SubmitButton;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class MassDeleteForm extends AdminForm
{
    /**
     * @throws
     */
    public function __construct($view, $name)
    {
        parent::__construct($view, $name);

        $this->data->deleteConfirm = $this->i18n->areYouSureToDeleteAllTheSelectedItems;
        $this->data->deleteButton = DIContainer::resolve(
            SubmitButton::class, $this,
            'AdminEditDeleteButton', $this->i18n->delete, 'Delete'
        );
        $this->data->deleteButton->setClasses([
            'btn', 'btn-outline', 'btn-danger'
        ]);
        $this->data->deleteButton->setOnClick("function()
        {
            return confirm('{$this->data->deleteConfirm}');
        }");
        $this->data->deleteButton->setOnSubmit(function() use ($view)
        {
            $view->preventDefault();
            $view->onBtnDeleteClick();
        });

        $this->data->cancelLabel = $this->i18n->cancel;

        $this->setContentView($this->getLayoutPath() . 'MassDeleteForm.html');
    }

    /**
     * @throws
     */
    public function getLayoutPath()
    {
        return ADMIN_RES_PATH . "layouts/general/listall/";
    }

    /**
     * @throws
     */
    public function getScriptPath()
    {
        return ADMIN_RES_PATH . "scripts/general/listall/";
    }

    /**
     * Event when the form is submitted
     *
     * @throws
     */
    public function onSubmit()
    {
        if (!$this->isSubmitted())
        {
            return;
        }

        $this->data->deleteButton->onSubmit();
    }
}
