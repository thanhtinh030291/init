<?php

namespace Lza\App\Admin\Modules\General\Listall;


use Lza\App\Admin\Elements\AdminForm;
use Lza\LazyAdmin\Form\SubmitButton;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class MassEditForm extends AdminForm
{
    private $updateFields;

    /**
     * @throws
     */
    public function __construct($view, $name)
    {
        parent::__construct($view, $name);

        $updateConfirm = $this->i18n->areYouSureToUpdateAllTheSelectedItems;
        $this->data->updateButton = DIContainer::resolve(
            SubmitButton::class, $this,
            'AdminListMassUpdateButton', $this->i18n->update, 'Update'
        );
        $this->data->updateButton->setClasses([
            'btn', 'btn-outline', 'btn-warning'
        ]);
        $this->data->updateButton->setOnClick("function()
        {
            return confirm('{$updateConfirm}');
        }");
        $this->data->updateButton->setOnSubmit(function() use ($view)
        {
            $view->preventDefault();
            $view->onBtnUpdateClick();
        });

        $this->data->cancelLabel = $this->i18n->cancel;
        $this->data->infoLabel = $this->i18n->clickOnTheCheckboxBeforeEachFieldsToEnableThem;
        $this->data->warningLabel = $this->i18n->enabledFieldsWithEmptyValue;

        $this->inputs = [];

        $this->setContentView($this->getLayoutPath() . 'MassEditForm.html');

        $this->setOnSubmit("function(form, event)
        {
            $('#loading-modal').modal('show');

            var selectedRows = table.column(0).checkboxes.selected();
            $.each(selectedRows, function(index, rowId)
            {
                $(form).append(
                    $('<input>').attr('type', 'hidden').attr('name', 'rows[]').val(rowId)
                );
            });

            event.preventDefault();
            $(form).submit();
            return true;
        }");
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
     * @throws
     */
    public function setInputs($fields)
    {
        $region = $this->env->region;
        $module = $this->env->module;
        $view = $this->env->view;

        $this->inputs = [];
        $updateFields = $this->isPost() && isset($this->request->updates)
                ? $this->request->updates : [];
        foreach ($fields as $field)
        {
            $field['field_folder'] = 'field/edit';
            $field['form'] = 'massedit';
            $field['set_form_value'] = in_array($field['field'], $updateFields);

            $class = sprintf(
                "Lza\\App\Admin\\Elements\\MassEdit\\%sField",
                camel_case($field['type'], true)
            );
            $input = DIContainer::resolve($class, $this, $field);
            $this->inputs[] = $input;

            $this->session->set(
                "js.{$region}.{$module}.{$view}.{$field['field']}",
                $input->getContentScript()
            );
        }
        $this->data->inputs = implode('', $this->inputs);

        if (!$this->isSubmitted())
        {
            return;
        }

        $isError = false;
        foreach ($this->inputs as $input)
        {
            $errors = $input->getErrors();
            if (strlen($errors))
            {
                $isError = true;
                $this->logger->log(LogLevel::ERROR, $errors);
            }
        }
        if ($isError)
        {
            return;
        }

        $this->data->updateButton->onSubmit();
    }
}
