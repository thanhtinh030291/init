<?php

namespace Lza\App\Admin\Modules\Setting\Edit;


use Lza\App\Admin\Elements\AdminForm;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SettingEditForm extends AdminForm
{
    /**
     * @throws
     */
    public function __construct($view, $name)
    {
        parent::__construct($view, $name);
        $view->preventDefault();

        $this->data->updateLabel = $this->i18n->update;
        $this->data->updateConfirm = $this->i18n->areYouSureToUpdateThisItem;

        $this->inputs = [];
        $this->values = [];

        $this->setContentView($this->getLayoutPath() . 'EditForm.html');
    }

    /**
     * @throws
     */
    public function getLayoutPath()
    {
        return ADMIN_RES_PATH . "layouts/setting/edit/";
    }

    /**
     * @throws
     */
    public function getScriptPath()
    {
        return ADMIN_RES_PATH . "scripts/setting/edit/";
    }

    /**
     * @throws
     */
    public function setInputs($fields)
    {
        $region = $this->env->region;
        $module = $this->env->module;
        $view = $this->env->view;

        $inputs = [];
        foreach ($fields as $field)
        {
            $field['field_folder'] = 'field';
            $field['mandatory'] = 0;
            $field['set_form_value'] = array_key_exists($field['id'], $this->isPost() ? $_POST : $_GET);

            $class = sprintf("Lza\\App\Admin\\Elements\\Setting\\%sField", camel_case($field['type'], true));
            $input = DIContainer::resolve($class, $this, $field);
            $this->inputs[] = $input;

            $this->session->set("js.{$region}.{$module}.{$view}.{$field['id']}", $input->getContentScript());
        }
        $this->data->inputs = implode('', $this->inputs);

        $isError = false;
        foreach ($this->inputs as $input)
        {
            $errors = $input->getErrors();
            if (strlen($errors))
            {
                $isError = true;
                $this->logger->log(LogLevel::ERROR, $$errors);
            }
        }
        if ($isError)
        {
            return;
        }
    }
}
