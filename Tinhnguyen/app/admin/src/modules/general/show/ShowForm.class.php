<?php

namespace Lza\App\Admin\Modules\General\Show;


use Lza\App\Admin\Elements\AdminForm;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ShowForm extends AdminForm
{
    /**
     * @var integer Item's ID to be edited
     */
    protected $id;

    /**
     * @var array List of inputs to be shown
     */
    protected $inputs;

    /**
     * @var array List of inputs' value
     */
    protected $values;

    /**
     * @throws
     */
    public function __construct($view, $name)
    {
        parent::__construct($view, $name);

        $id = $this->env->id;
        $viewData = $view->getData();
        $settings = $viewData->table['settings'];
        $options = empty($settings) ? [] : $this->encryptor->jsonDecode($settings);

        $this->data->returnButton = '';
        $returnLabel = $this->i18n->return;
        $returnLink = str_replace("/show/{$id}", '/list', WEBSITE_URL);
        $this->data->returnButton = "
            <a class='btn btn-outline btn-success' href='{$returnLink}'>
                {$returnLabel}
            </a>
        ";

        $this->data->addButton = '';
        $addLink = str_replace("/show/{$id}", '/add', WEBSITE_URL);
        if (!isset($options->add) || $options->add)
        {
            $addLabel = $this->i18n->add;
            $this->data->addButton = "
                <a href='{$addLink}' class='btn btn-outline btn-primary'>
                    {$addLabel}
                </a>
            ";
        }

        $this->data->editButton = '';
        $editLink = str_replace("/show/", '/edit/', WEBSITE_URL);
        if (!isset($options->edit) || $options->edit)
        {
            $editLabel = $this->i18n->edit;
            $this->data->editButton = "
                <a href='{$editLink}' class='btn btn-outline btn-warning'>
                    {$editLabel}
                </a>
            ";
        }

        $this->data->deleteButton = '';
        $viewData = $view->getData();
        if (!isset($options->delete) || $options->delete)
        {
            $deleteLabel = $this->i18n->delete;
            $deleteConfirm = $this->i18n->areYouSureToDeleteThisItem;
            $deleteLink = str_replace('/show/', '/edit/', WEBSITE_URL)
                        . '?action=Delete';
            $this->data->deleteButton = "
                <a href='{$deleteLink}'
                    class='btn btn-outline btn-danger'
                    onClick=\"return confirm('{$deleteConfirm}')\" >
                     {$deleteLabel}
                </a>
            ";
        }

        $this->inputs = [];
        $this->values = ['id' => $id];

        $this->setContentView($this->getLayoutPath() . 'ShowForm.html');
    }

    /**
     * @throws
     */
    public function getLayoutPath()
    {
        return ADMIN_RES_PATH . "layouts/general/show/";
    }

    /**
     * @throws
     */
    public function getScriptPath()
    {
        $ds = DIRECTORY_SEPARATOR;
        return ADMIN_RES_PATH . "scripts/general/show/";
    }

    /**
     * @throws
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @throws
     */
    public function getManyToManyValues()
    {
        return $this->manyToManyValues;
    }

    /**
     * @throws
     */
    public function setInputs($fields)
    {
        $region = $this->env->region;
        $module = $this->env->module;
        $view = $this->env->view;

        $item = $this->view->doGetItem($this->values['id']);
        if (empty($item))
        {
            $this->data->inputs = '';
            $this->data->editButton = '';
            $this->data->deleteButton = '';
            return;
        }

        foreach ($fields as $field)
        {
            $field['field_folder'] = 'field/latest';
            $field['form'] = 'show';
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

        foreach ($this->inputs as $input)
        {
            $name = $input->getName();
            $value = $input->getValue();
            switch (substr(strrchr(get_class($input), "\\"), 1))
            {
                case 'HasField':
                    break;
                case 'HaveField':
                    $this->manyToManyValues[$name] = $value;
                    break;
                case 'BelongField':
                case 'WeakelongField':
                    $this->values["{$name}_id"] = $value;
                    break;
                case 'EnumsField':
                    $this->values[$name] = $this->encryptor->jsonEncode($value);
                    break;
                case 'PasswordField':
                    if (strlen($value) > 0)
                    {
                        $this->values[$name] = $value;
                    }
                    break;
                default:
                    $this->values[$name] = $value;
            }
        }
    }

    /**
     * @throws
     */
    public function generateFormValidations($rules, $messages)
    {
        // Do nothing
    }
}
