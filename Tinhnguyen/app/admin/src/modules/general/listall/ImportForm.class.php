<?php

namespace Lza\App\Admin\Modules\General\Listall;


use Box\Spout\Common\Type;
use ErrorException;
use Lza\App\Admin\Elements\AdminForm;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Form\SubmitButton;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;
use Lza\LazyAdmin\Utility\Tool\SpoutHandler;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ImportForm extends AdminForm
{
    /**
     * @throws
     */
    public function __construct($view, $name)
    {
        parent::__construct($view, $name);

        $importConfirm = $this->i18n->areYouSureToUpdateAllTheSelectedItems;
        $this->data->importButton = DIContainer::resolve(
            SubmitButton::class, $this,
            'AdminListImportButton', $this->i18n->importFile, 'Import'
        );
        $this->data->importButton->setClasses([
            'btn', 'btn-outline', 'btn-primary'
        ]);
        $this->data->importButton->setOnClick("function()
        {
            return confirm('{$importConfirm}');
        }");

        $this->data->inputLabel = $this->i18n->pleaseSpecifyExcelPath;
        $this->data->importLabel = $this->i18n->importFile;
        $this->data->cancelLabel = $this->i18n->cancel;
        $this->data->truncateLabel = $this->i18n->truncateAllData;
        $this->data->onDuplicatedLabel = $this->i18n->onDuplicated;
        $this->data->updateLabel = $this->i18n->update;
        $this->data->ignoreLabel = $this->i18n->ignore;

        $this->inputs = [];

        $this->setContentView($this->getLayoutPath() . 'ImportForm.html');
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
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Event when the form is submitted
     *
     * @throws
     */
    public function onSubmit($table, $fields)
    {
        if (!$this->isSubmitted())
        {
            return;
        }
        $this->view->preventDefault();

        $excelFile = urldecode($this->request->post->import);
        $excelFile = str_replace(WEBSITE_ROOT . 'root/', DOCUMENT_ROOT, $excelFile);
        if (!is_file($excelFile))
        {
            throw new ErrorException('File does not exists!');
        }

        $truncate = $this->request->post->truncateAll;
        if (strlen($truncate) > 0)
        {
            $this->view->doTruncate();
        }

        $onDuplicated = $this->request->post->onDuplicated;

        $fileType = pathinfo($excelFile, PATHINFO_EXTENSION);
        $excelTypes = [
            'xlsx' => Type::XLSX,
            'csv' => Type::CSV,
            'ods' => Type::ODS
        ];
        if (!in_array($fileType, array_keys($excelTypes)))
        {
            throw new ErrorException('The uploaded file is not in excel format!');
        }

        $headers = [];

        $handler = DIContainer::resolve(SpoutHandler::class, $excelTypes[$fileType]);
        $handler->read(
            $excelFile,
            function($rowNo, $columns) use ($table, $fields, $onDuplicated, &$headers)
            {
                $this->readRow($rowNo, $columns, $table, $fields, $onDuplicated, $headers);
            }
        );
    }

    /**
     * @throws
     */
    private function readRow($rowNo, $columns, $table, $fields, $onDuplicated, &$headers)
    {
        if ($rowNo === 1)
        {
            $this->readHeader($fields, $columns, $headers);
        }
        else
        {
            $this->readRecord($table, $headers, $columns, $onDuplicated);
        }
    }

    /**
     * @throws
     */
    private function readHeader($fields, $columns, &$headers)
    {
        foreach ($fields as $field)
        {
            if ($field['field'] === 'id')
            {
                continue;
            }

            $header = [
                'table' => $field['table'],
                'field' => $field['field'],
                'single' => $field['single'],
                'plural' => $field['plural'],
                'type' => $field['type'],
                'mandatory' => $field['mandatory'],
                'unique' => $field['unique'],
                'minlength' => $field['minlength'],
                'maxlength' => $field['maxlength'],
                'display' => $field['display'],
                'field_note' => $field['field_note'],
                'form' => 'import'
            ];

            $index = 0;
            foreach ($columns as $column)
            {
                if (
                    (
                        in_array($field['type'], ['have', 'enums']) &&
                        $field['plural'] === $column
                    ) ||
                    (
                        !in_array($field['type'], ['have', 'enums']) &&
                        $field['single'] === $column
                    )
                )
                {
                    $header['index'] = $index;
                }
                $index++;
            }
            $headers[] = $header;
        }
    }

    /**
     * @throws
     */
    private function readRecord($table, $fields, $item, $onDuplicated)
    {
        $this->values = [];
        $this->manyToManyValues = [];
        $this->validations = [];
        $this->inputs = [];
        foreach ($fields as $field)
        {
            $class = sprintf(
                "Lza\\App\Admin\\Elements\\Import\\%sField",
                camel_case(
                    $field['field'] === 'id' ? 'id' : $field['type'], true
                )
            );
            $input = DIContainer::resolve($class, $this, $field, $item);
            $this->inputs[] = $input;
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

        $conditions = [];
        $params = [];
        if ($table['unique_keys'] !== '')
        {
            $uniqueFields = explode(',', $table['unique_keys']);
            foreach ($uniqueFields as $uniqueField)
            {
                $conditions[] = $uniqueField . ' = ?';
                $params[] = $this->values[$uniqueField];
            }
            $model = ModelPool::getModel($table['id']);
            $affectedItems = call_user_func_array(
                [$model, 'where'],
                array_merge([implode(' and ', $conditions)], $params)
            );
        }

        if (!empty($affectedItems) && count($affectedItems) > 0)
        {
            if ($onDuplicated === 'update')
            {
                foreach ($affectedItems as $affectedItem)
                {
                    $this->view->doSave(
                        array_merge($this->values, ['id' => $affectedItem['id']]),
                        $this->manyToManyValues
                    );
                }
            }
        }
        else
        {
            $this->view->doSave($this->values, $this->manyToManyValues);
        }

        return true;
    }
}
