<?php

namespace Lza\App\Admin\Modules\General\Add;


use Lza\App\Admin\Elements\AdminForm;
use Lza\LazyAdmin\Form\SubmitButton;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class AddForm extends AdminForm
{
    /**
     * @throws
     */
    public function __construct($view, $name)
    {
        parent::__construct($view, $name);

        $region = $this->env->region;
        $module = $this->env->module;

        $this->data->saveButton = DIContainer::resolve(
            SubmitButton::class, $this,
            'AdminAddSaveButton', $this->i18n->save, 'Save'
        );
        $this->data->saveButton->setClasses([
            'btn', 'btn-outline', 'btn-primary'
        ]);
        $this->data->saveButton->setOnSubmit(function() use ($view)
        {
            $view->preventDefault();
            $view->onBtnSaveClick();
        });

        $this->data->cancelLabel = $this->i18n->cancel;
        $this->data->cancelLink = str_replace('/add', '', WEBSITE_URL);

        $this->data->reloadLabel = $this->i18n->reload;
        $this->data->reloadLink = WEBSITE_URL;

        $this->inputs = [];

        $this->setContentView($this->getLayoutPath() . 'AddForm.html');
    }

    /**
     * @throws
     */
    public function getLayoutPath()
    {
        return ADMIN_RES_PATH . "layouts/general/add/";
    }

    /**
     * @throws
     */
    public function getScriptPath()
    {
        return ADMIN_RES_PATH . "scripts/general/add/";
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
        foreach ($fields as $field)
        {
            $field['field_folder'] = 'field';
            $field['form'] = 'add';
            $class = sprintf(
                "Lza\\App\Admin\\Elements\\General\\%sField",
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

        $this->data->saveButton->onSubmit();
    }
}
