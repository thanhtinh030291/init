<?php

namespace Lza\App\Admin\Modules\General\Edit;


use Lza\App\Admin\Elements\AdminForm;
use Lza\LazyAdmin\Form\SubmitButton;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class EditForm extends AdminForm
{
    /**
     * @var integer Item's ID to be edited
     */
    protected $id;

    /**
     * @throws
     */
    public function __construct($view, $name)
    {
        parent::__construct($view, $name);

        $id = $this->env->id;

        $updateConfirm = $this->i18n->areYouSureToUpdateThisItem;
        $this->data->updateButton = DIContainer::resolve(
            SubmitButton::class, $this,
            'AdminEditUpdateButton', $this->i18n->update, 'Update'
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

        $this->data->deleteButton = '';
        $viewData = $view->getData();
        $settings = $viewData->table['settings'];
        $options = empty($settings) ? [] : $this->encryptor->jsonDecode($settings);
        if (!isset($options->delete) || $options->delete)
        {
            $deleteConfirm = $this->i18n->areYouSureToDeleteThisItem;
            $this->data->deleteButton = DIContainer::resolve(
                SubmitButton::class, $this,
                'AdminEditDeleteButton', $this->i18n->delete, 'Delete'
            );
            $this->data->deleteButton->setClasses([
                'btn', 'btn-outline', 'btn-danger'
            ]);
            $this->data->deleteButton->setOnClick("function()
            {
                return confirm('{$deleteConfirm}');
            }");
            $this->data->deleteButton->setOnSubmit(function() use ($view)
            {
                $view->preventDefault();
                $view->onBtnDeleteClick();
            });
        }

        $this->data->reloadLabel = $this->i18n->reload;
        $this->data->reloadLink = WEBSITE_URL;

        $this->data->cancelLabel = $this->i18n->cancel;
        $this->data->cancelLink = str_replace('/edit/', '/show/', WEBSITE_URL);

        $this->data->returnLabel = $this->i18n->return;
        $this->data->returnLink = str_replace("/edit/{$id}", '/list', WEBSITE_URL);

        $this->inputs = [];
        $this->values = ['id' => $id];
        $this->manyToManyValues = [];

        $this->setContentView($this->getLayoutPath() . 'EditForm.html');
    }

    /**
     * @throws
     */
    public function getLayoutPath()
    {
        return ADMIN_RES_PATH . "layouts/general/edit/";
    }

    /**
     * @throws
     */
    public function getScriptPath()
    {
        return ADMIN_RES_PATH . "scripts/general/edit/";
    }

    /**
     * @throws
     */
    public function setInputs($fields)
    {
        $region = $this->env->region;
        $module = $this->env->module;
        $view = $this->env->view;

        $item = null;
        if (!$this->isSubmitted())
        {
            $item = $this->view->doGetItem($this->values['id']);
        }

        $this->inputs = [];
        foreach ($fields as $field)
        {
            if ($field['type'] === 'password')
            {
                $field['mandatory'] = 0;
            }
            $field['field_folder'] = 'field';
            $field['form'] = 'edit';
            $class = sprintf(
                "Lza\\App\Admin\\Elements\\General\\%sField",
                camel_case(
                    $field['field'] === 'id' ? $field['field'] : $field['type'], true
                )
            );
            $input = DIContainer::resolve($class, $this, $field, $item);
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

        $this->data->updateButton->onSubmit();
        if ($this->data->deleteButton !== '')
        {
            $this->data->deleteButton->onSubmit();
        }
    }
}
