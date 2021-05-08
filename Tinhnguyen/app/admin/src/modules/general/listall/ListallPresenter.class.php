<?php

namespace Lza\App\Admin\Modules\General\Listall;


use Box\Spout\Common\Type;
use Lza\App\Admin\Modules\AdminPresenter;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\SpoutHandler;

/**
 * Handle Default action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ListallPresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Get Filters request
     *
     * @throws
     */
    public function doGetFilters($username, $moduleId)
    {
        $model = ModelPool::getModel('lzafilter');
        return $model->order("lzafilter.order_by")->where([
            "user.username" => $username,
            "lzamodule.id" => $moduleId
        ]);
    }

    /**
     * @throws
     */
    protected function export($items, $fields, $exportType, $exportAllVersions)
    {
        $table = $this->doGetTable($this->module);
        $headerTitle = $table['plural'];
        $companyName = $this->setting->companyName;
        if (strlen($companyName) === 0)
        {
            $companyName = 'Nghiem Le';
        }

        $now = date('YmdHis');
        $types = [
            'xlsx' => Type::XLSX,
            'ods' => Type::ODS,
            'csv' => Type::CSV
        ];
        $handler = DIContainer::resolve(SpoutHandler::class, $types[$exportType]);
        $handler->openToBrowser("{$headerTitle} Export {$now}.{$exportType}");

        $headers = [];
        foreach ($fields as $field)
        {
            if ($field['type'] === 'password')
            {
                continue;
            }
            $headers[] = in_array($field['type'], ['has', 'have'])
                    ? $field['plural']
                    : $field['single'];
        }
        if ($exportAllVersions)
        {
            $headers[] = 'Version';
            $headers[] = 'Modified Time';
            $headers[] = 'Action';
        }
        $handler->addRow($headers);

        foreach ($items as $item)
        {
            $columns = [];
            foreach ($fields as $field)
            {
                if ($field['type'] === 'password')
                {
                    continue;
                }

                if ($field['type'] === 'date')
                {
                    $columns[] = $item[$field['field']] === null
                            ? '' : $item[$field['field']]->format(DATE_FORMAT);
                }
                elseif (in_array($field['type'], ['datetime', 'eventstart', 'eventend']))
                {
                    $columns[] = $item[$field['field']] === null
                            ? '' : $item[$field['field']]->format(DATE_FORMAT);
                }
                elseif ($field['type'] === 'belong')
                {
                    $refTable = $field['field'];
                    $refModel = ModelPool::getModel($refTable);
                    $refItems = $refModel->where("id", $item["{$field['field']}_id"]);
                    $refItem = $refItems->fetch();
                    $columns[] = $refItem[$field['display']];
                }
                elseif ($field['type'] === 'weakbelong')
                {
                    $fieldParts = explode(':', $field['field']);
                    $refTable = $fieldParts[0];
                    $refModel = ModelPool::getModel($refTable);
                    $refItems = $refModel->where(
                        "id", $item["{$fieldParts[0]}_id"]
                    );
                    $refItem = $refItems->fetch();
                    $columns[] = $refItem[$field['display']];
                }
                elseif ($field['type'] === 'self')
                {
                    $refTable = $field['table'];
                    $refModel = ModelPool::getModel($refTable);
                    $refItems = $refModel->where(
                        "id", $item[$field['field']]
                    );
                    $refItem = $refItems->fetch();
                    $columns[] = $refItem[$field['display']];
                }
                elseif ($field['type'] === 'has')
                {
                    $refTable = $field['field'];
                    $refModel = ModelPool::getModel($refTable);
                    $refItems = $refModel->where(
                        "{$field['table']}_id", $item['id']
                    );
                    $displayValues = [];
                    foreach ($refItems as $refItem)
                    {
                        $displayValues[] = $refItem[$field['display']];
                    }
                    $columns[] = implode("\n", $displayValues);
                }
                elseif ($field['type'] === 'have')
                {
                    $refTable = $field['field'];
                    $refItemTable = trim(
                        str_replace(
                            $field['table'], '',
                            $field['field']
                        ),
                        '_'
                    );
                    $refModel = ModelPool::getModel($refTable);
                    $refItems = $refModel->where(
                        $field['table'] . "_id", $item['id']
                    );
                    $displayValues = [];
                    foreach ($refItems as $refItem)
                    {
                        $refItemModel = ModelPool::getModel($refItemTable);
                        $refItems = $refItemModel->where(
                            "id", $refItem[$refItemTable . '_id']
                        );
                        $refItem = $refItems->fetch();
                        $displayValues[] = $refItem[$field['display']];
                    }
                    $columns[] = implode("\n", $displayValues);
                }
                elseif ($field['type'] === 'enums')
                {
                    $columns[] = implode(', ', $item[$field['field']]);
                }
                elseif ($field['type'] === 'json')
                {
                    $columns[] = $this->encryptor->jsonEncode($item[$field['field']]);
                }
                elseif ($field['type'] === 'checkbox')
                {
                    $columns[] = $item[$field['field']] === 0 ? 'No' : 'Yes';
                }
                else
                {
                    $columns[] = $item[$field['field']];
                }
            }

            if ($exportAllVersions)
            {
                $columns[] = $item['revision'];
                $columns[] = $item['valid_from'];
                $columns[] = $item['action'];
            }

            $handler->addRow($columns);
        }
        $handler->close();
    }
}
