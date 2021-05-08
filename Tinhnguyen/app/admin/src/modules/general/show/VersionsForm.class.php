<?php

namespace Lza\App\Admin\Modules\General\Show;


use Lza\App\Admin\Elements\AdminForm;
use Lza\App\Admin\Elements\History\DatetimeField;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class VersionsForm extends AdminForm
{
    /**
     * @throws
     */
    public function __construct($view, $name, $itemLabel)
    {
        parent::__construct($view, $name);

        $this->data->itemLabel = $itemLabel;

        $this->setContentView(
            $this->getLayoutPath() . 'VersionsForm.html'
        );
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
        return ADMIN_RES_PATH . "scripts/general/show/";
    }

    /**
     * @throws
     */
    public function setInputs($fields)
    {
        $id = $this->env->id;

        $items = $this->view->doGetHistory($id);

        $this->data->selectForm = '';
        $this->data->versions = '';

        if (count($items) <= 1)
        {
            return;
        }

        $this->onCreateSelectForm($items);
        $this->onCreateVersions($items, $fields);
    }

    /**
     * Event when Select Options is creating
     *
     * @throws
     */
    public function onCreateSelectForm($items)
    {
        $selectFormOptions = [];
        $i = 0;
        foreach ($items as $item)
        {
            $i++;
            if ($i > 1)
            {
                $action = $this->i18n->{$item['action']};
                $selectFormOptions[] = "
                    <option value='{$item['id']}'>
                        {$item['id']}: {$action}
                    </option>";
            }
        }
        $selectFormLayout = file_get_contents(
            $this->getLayoutPath() . 'VersionSelectForm.html'
        );
        $selectFormLayout = str_replace(
            '{$revisionLabel}',
            $this->i18n->revision,
            $selectFormLayout
        );
        $selectFormLayout = str_replace(
            '{$latestLabel}',
            $this->i18n->latest,
            $selectFormLayout
        );
        $selectFormLayout = str_replace(
            '{$options}',
            implode('', $selectFormOptions),
            $selectFormLayout
        );

        $this->data->selectForm = $selectFormLayout;
    }

    /**
     * Event when Versions is creating
     *
     * @throws
     */
    public function onCreateVersions($items, $fields)
    {
        $versions = [];
        foreach ($items as $item)
        {
            $inputs = [];
            foreach ($fields as $field)
            {
                $field['field_folder'] = 'field/history';
                $field['form'] = 'version';
                $class = sprintf(
                    "Lza\\App\\Admin\\Elements\\History\\%sField",
                    camel_case(
                        $field['field'] === 'id' ? $field['field'] : $field['type'], true
                    )
                );
                $inputs[] = DIContainer::resolve($class, $this, $field, $item);
            }

            $modifiedTimeField = [
                'field' => 'valid_from',
                'field_folder' => 'field/history',
                'type' => 'datetime',
                "single{$this->session->lzalanguage}" => $this->i18n->modifiedTime,
                'mandatory' => 0,
                'unique' => 0,
                'field_note' => ''
            ];
            $inputs[] = DIContainer::resolve(
                DatetimeField::class, $this,
                $modifiedTimeField, $item
            );

            $version = implode('', $inputs);
            $versions[] = "
                <div class='panel-body' id='version-{$item['id']}'>
                    <div class='row'>{$version}</div>
                </div>
                <script type='text/javascript'>
                    $(document).ready(function()
                    {
                        $('#version-{$item['id']}').addClass('hide');
                    });
                </script>";
        }
        $this->data->versions = implode('', $versions);
    }

    /**
     * @throws
     */
    public function generateFormValidations($rules, $messages)
    {
        // Do nothing
    }
}
