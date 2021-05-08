<?php

namespace Lza\App\Admin\Modules;


use Exception;
use Lza\Config\Models\ModelPool;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait AdminControllerTrait
{
    /**
     * @var array List of Filtered Database Table's fields information
     */
    protected $tableFields;

    /**
     * @var array List of All Database Table's fields
     */
    protected $allTableFields;

    /**
     * @var array List of Sequential Fields
     */
    protected $sequentialFields = [];

    /**
     * @throws
     */
    public function __construct()
    {
        parent::__construct();
        $this->sequentialFields = [];
    }

    /**
     * @throws
     */
    public function getDatabaseInfo()
    {
        return $this->model->getDatabaseInfo();
    }

    /**
     * @throws
     */
    public function getTableFields($module = null, $conditions = [])
    {
        if (isset($this->tableFields))
        {
            return $this->tableFields;
        }
        $tableFields = $this->model->getTableFields($module === null ? $this->module : $module, $conditions);

        foreach ($tableFields as $tableField)
        {
            if ($tableField['type'] === 'sequence' && !in_array($tableField, $this->sequentialFields))
            {
                $this->sequentialFields[] = $tableField;
            }
        }
        return $tableFields;
    }

    /**
     * @throws
     */
    public function getAllTableFields($module = null)
    {
        if (isset($this->allTableFields))
        {
            return $this->allTableFields;
        }
        $allTableFields = $this->model->getAllTableFields($module === null ? $this->module : $module);

        return $allTableFields;
    }

    /**
     * @throws
     */
    public function getList(
        $callback, $page = null, $size = null, $fields = null,
        $conditions = null, $orders = null, $level = null
    )
    {
        $list = $this->model->getList($page, $size, $fields, $conditions, $orders, $level);
        if (!$list)
        {
            $callback->onError($this->i18n->errorListingData);
            return null;
        }

        $items = [];
        foreach ($list as $item)
        {
            $items[] = $this->formatOutputs($item);
        }
        return $items;
    }

    /**
     * @throws
     */
    public function getListHistory($callback, $ids = null)
    {
        $list = $this->model->getListHistory($ids);
        if (!$list)
        {
            $callback->onError($this->i18n->errorListingData);
            return null;
        }

        $items = [];
        try
        {
            foreach ($list as $item)
            {
                $items[] = $this->formatOutputs($item);
            }
        }
        catch (Exception $e)
        {
            return $this->getList(
                $callback, null, null,
                "*, 1 revision, crt_at valid_from, 'Created' action",
                $ids !== null ? ['id in' => $ids] : null
            );
        }
        return $items;
    }

    /**
     * @throws
     */
    public function getRevision($callback, $id = null)
    {
        $revision = $this->model->getRevision($id);
        if (!$revision)
        {
            $callback->onError($this->i18n->errorListingData);
            return null;
        }
        return $revision;
    }

    /**
     * @throws
     */
    public function get($callback, $id)
    {
        $item = $this->model->get($id)->fetch();
        if (!$item)
        {
            $callback->onError($this->i18n->itemIdIsNotExisted($id));
            return false;
        }
        return $this->formatOutputs($item);
    }

    /**
     * @throws
     */
    public function getItemHistory($callback, $id)
    {
        $items = $this->model->getItemHistory($id);
        if (!$items)
        {
            $callback->onError($this->i18n->itemIdIsNotExisted($id));
            return false;
        }
        $versions = [];
        foreach ($items as $item)
        {
            $item['valid_from'] = date_create_from_format('Y-m-d H:i:s', $item['valid_from']);
            $versions[] = $this->formatOutputs($item);
        }
        return $versions;
    }

    /**
     * @throws
     */
    public function insert($callback, $user, $item, $many = null)
    {
        unset($item["id"]);
        $item = $this->formatInputs($item);
        $table = $this->getTable();
        $model = ModelPool::getModel($table['id']);

        foreach ($this->sequentialFields as $sequentialField)
        {
            $field = $sequentialField['field'];
            if (strlen($sequentialField['display']) === 0)
            {
                $affectedItems = $model->where("{$field} >= ?", $item[$field]);
                foreach ($affectedItems as $affectedItem)
                {
                    $affectedItem->update([
                        "{$field}" => ($affectedItem[$field] + 1)
                    ]);
                }
            }
            else
            {
                if ($item[$sequentialField['display']] === null)
                {
                    $affectedItems = $model->where(
                        "{$field} >= ? and {$sequentialField['display']} is null",
                        $item[$field]
                    );
                    foreach ($affectedItems as $affectedItem)
                    {
                        $affectedItem->update([
                            $field => ($affectedItem[$field] + 1)
                        ]);
                    }
                }
                else
                {
                    $affectedItems = $model->where(
                        "{$field} >= ? and {$sequentialField['display']} = ?",
                        $item[$field], $item[$sequentialField['display']]
                    );
                    foreach ($affectedItems as $affectedItem)
                    {
                        $affectedItem->update([
                            $field => ($affectedItem[$field] + 1)
                        ]);
                    }
                }
            }
        }

        $result = $this->model->create($user, $item, $many);
        if (!$result)
        {
            $callback->onError($this->i18n->failedToCreateItem);
            return false;
        }

        $callback->onSaveSuccess($result);
        return true;
    }

    /**
     * @throws
     */
    public function update($callback, $user, $item, $many = null)
    {
        $item = $this->formatInputs($item);
        $original = $this->model->get($item['id'])->fetch();
        $table = $this->getTable();
        $model = ModelPool::getModel($table['id']);

        // Update Node's Siblings
        foreach ($this->sequentialFields as $sequentialField)
        {
            $field = $sequentialField['field'];
            if (strlen($sequentialField['display']) === 0)
            {
                $affectedItems = $model->where("{$field} > ?", $original[$field]);
                foreach ($affectedItems as $affectedItem)
                {
                    $affectedItem->update([
                        "{$field}" => ($affectedItem[$field] - 1)
                    ]);
                }

                $affectedItems = $model->where("{$field} >= ?", $item[$field]);
                foreach ($affectedItems as $affectedItem)
                {
                    $affectedItem->update([
                        $field => ($affectedItem[$field] + 1)
                    ]);
                }
            }
            else
            {
                if ($original[$sequentialField['display']] === null)
                {
                    $affectedItems = $model->where(
                        "{$field} > ? and {$sequentialField['display']} is null",
                        $original[$field]
                    );
                    foreach ($affectedItems as $affectedItem)
                    {
                        $affectedItem->update([
                            $field => ($affectedItem[$field] - 1)
                        ]);
                    }
                }
                else
                {
                    $affectedItems = $model->where(
                        "{$field} > ? and {$sequentialField['display']} = ?",
                        $original[$field], $original[$sequentialField['display']]
                    );
                    foreach ($affectedItems as $affectedItem)
                    {
                        $affectedItem->update([
                            $field => ($affectedItem[$field] - 1)
                        ]);
                    }
                }

                if ($item[$sequentialField['display']] === null)
                {
                    $affectedItems = $model->where(
                        "{$field} >= ? and {$sequentialField['display']} is null",
                        $item[$field]
                    );
                    foreach ($affectedItems as $affectedItem)
                    {
                        $affectedItem->update([
                            $field => ($affectedItem[$field] + 1)
                        ]);
                    }
                }
                else
                {
                    $affectedItems = $model->where(
                        "{$field} >= ? and {$sequentialField['display']} = ?",
                        $item[$field], $item[$sequentialField['display']]
                    );
                    foreach ($affectedItems as $affectedItem)
                    {
                        $affectedItem->update([
                            $field => ($affectedItem[$field] + 1)
                        ]);
                    }
                }
            }
        }

        // Update Node
        $changes = [];
        foreach ($item as $key => $value)
        {
            if ("{$original[$key]}" !== "{$value}")
            {
                $changes[$key] = $value;
            }
        }
        $result = $this->model->modify($user, $original, $changes, $many);
        if (!$result)
        {
            $callback->onError($this->i18n->failedToUpdateItem);
            return false;
        }
        $callback->onSaveSuccess($result);
        return true;
    }

    /**
     * @throws
     */
    public function updateAll($callback, $user, $ids, $item)
    {
        $item = $this->formatInputs($item);
        foreach ($ids as $id)
        {
            $original = $this->model->get($id)->fetch();
            $changes = [];
            foreach ($item as $key => $value)
            {
                if ($original[$key] !== "{$value}")
                {
                    $changes[$key] = $value;
                }
            }
            $this->model->modify($user, $original, $changes);
        }
        $callback->onUpdateAllSuccess();
    }

    /**
     * @throws
     */
    public function truncate($callback, $user)
    {
        $table = $this->getTable();
        $model = ModelPool::getModel($table['id']);
        $model->where('1=1')->delete();
        $callback->onTruncateSuccess();
    }

    /**
     * @throws
     */
    public function delete($callback, $user, $id)
    {
        $table = $this->getTable();
        $model = ModelPool::getModel($table['id']);
        $item = $model->where('id = ?', $id)->fetch();

        foreach ($this->sequentialFields as $sequentialField)
        {
            if (strlen($sequentialField['display']) === 0)
            {
                $field = $sequentialField['field'];
                $affectedItems = $model->where("{$field} > ?", $item[$field]);
                foreach ($affectedItems as $affectedItem)
                {
                    $affectedItem->update([
                        $field => ($affectedItem[$field] - 1)
                    ]);
                }
            }
            else
            {
                $filters = [];
                $conditionalFields = explode(',', $sequentialField['display']);
                foreach ($conditionalFields as $conditionalField)
                {
                    $filters[$conditionalField] = $item[$conditionalField];
                }

                $field = $sequentialField['field'];
                if ($item[$sequentialField['field']] === null)
                {
                    $affectedItems = $model->where(
                        "{$field} > ? and {$sequentialField['display']} is null",
                        $item[$field]
                    );
                    foreach ($affectedItems as $affectedItem)
                    {
                        $affectedItem->update([
                            $field => ($affectedItem[$field] - 1)
                        ]);
                    }
                }
                else
                {
                    $affectedItems = $model->where(
                        "{$field} > ? and {$sequentialField['display']} = ?",
                        $item[$field],
                        $item[$sequentialField['display']]
                    );
                    foreach ($affectedItems as $affectedItem)
                    {
                        $affectedItem->update([
                            $field => ($affectedItem[$field] - 1)
                        ]);
                    }
                }
            }
        }

        $result = $this->model->remove($user, $id);

        if (!$result)
        {
            $callback->onError($this->i18n->cannotDeleteItemId($id));
            return false;
        }

        $callback->onDeleteSuccess($result);
        return true;
    }

    /**
     * @throws
     */
    public function deleteAll($callback, $user, $ids)
    {
        $result = $this->model->removeAll($user, $ids);
        $callback->onDeleteAllSuccess($result);
    }

    /**
     * @throws
     */
    protected function formatInputs($item)
    {
        $fields = $this->getAllTableFields($this->module);
        foreach ($fields as $field)
        {
            if (!isset($item[$field['field']]))
            {
                continue;
            }
            $dbInfo = $this->getDatabaseInfo();
            if (isset($item[$field['field']]) || isset($item["{$field['field']}_id"]))
            {
                if ($field['type'] === 'date')
                {
                    if ($dbInfo['database_info']['type'] === 'oracle')
                    {
                        $item[$field['field']] = !$item[$field['field']] || $item[$field['field']] === null
                                ? null : $item[$field['field']]->format('Y-m-d H:i:s');
                    }
                    else
                    {
                        $item[$field['field']] = !$item[$field['field']] || $item[$field['field']] === null
                                ? null : $item[$field['field']]->format('Y-m-d');
                    }
                }
                elseif (in_array($field['type'], ['datetime', 'eventstart', 'eventend']))
                {
                    $item[$field['field']] = !$item[$field['field']] || $item[$field['field']] === null
                            ? null : $item[$field['field']]->format('Y-m-d H:i:s');
                }
                elseif ($field['type'] === 'password')
                {
                    $item[$field['field']] = $this->encryptor->hash($item[$field['field']], 2);
                }
                elseif (
                    $field['type'] === 'belong' &&
                    $field['mandatory'] === 0 &&
                    $item["{$field['field']}_id"] === -1
                )
                {
                    $item["{$field['field']}_id"] = null;
                }
                elseif (
                    $field['type'] === 'self' &&
                    $field['mandatory'] === 0 &&
                    $item[$field['field']] === -1
                )
                {
                    $item[$field['field']] = null;
                }
                elseif ($field['type'] === 'html')
                {
                    $item[$field['field']] = str_replace(["\r", "\n"], '', $item[$field['field']]);
                }
                elseif (in_array($field['type'], ['enums', 'json']))
                {
                    $item[$field['field']] = $this->encryptor->jsonEncode($item[$field['field']]);
                }
                elseif ($field['type'] === 'checkbox' && $item[$field['field']] === "")
                {
                    $item[$field['field']] = 0;
                }
            }

            if (!in_array($field['type'], ['html', 'json']))
            {
                if (isset($item[$field['field']]))
                {
                    $item[$field['field']] = strip_tags($item[$field['field']]);
                }
                if (isset($item["{$field['field']}_id"]))
                {
                    $item["{$field['field']}_id"] = strip_tags($item["{$field['field']}_id"]);
                }
            }
        }
        return $item;
    }

    /**
     * @throws
     */
    protected function formatOutputs($item)
    {
        $fields = $this->getAllTableFields($this->module);
        foreach ($fields as $field)
        {
            if (!isset($item[$field['field']]))
            {
                continue;
            }
            if (isset($item[$field['field']]))
            {
                if ($field['type'] === 'password')
                {
                    $item[$field['field']] = '';
                }
                elseif ($field['type'] === 'html')
                {
                    $item[$field['field']] = htmlspecialchars_decode($item[$field['field']], 2);
                }
                elseif (in_array($field['type'], ['enums', 'json']))
                {
                    $item[$field['field']] = empty($item[$field['field']])
                            ? '' : $this->encryptor->jsonDecode($item[$field['field']], true);
                }
                elseif (in_array($field['type'], ['date']))
                {
                    $item[$field['field']] = $item[$field['field']] === null ? null : date_create_from_format(
                        'Y-m-d', $item[$field['field']]
                    );
                }
                elseif (in_array($field['type'], ['datetime', 'eventstart', 'eventend']))
                {
                    $item[$field['field']] = $item[$field['field']] === null ? null : date_create_from_format(
                        'Y-m-d H:i:s', $item[$field['field']]
                    );
                }
            }
        }
        return $item;
    }

    /**
     * @throws
     */
    public function save($callback, $user, $item, $many)
    {
        $action = !isset($item['id']) || $item['id'] <= 0 || $item['id'] === '' ? 'insert' : 'update';
        return $this->$action($callback, $user, $item, $many);
    }

    /**
     * @throws
     */
    public function moveInside($callback, $nodeId, $targetId, $selfField, $sequenceField)
    {
        $model = ModelPool::getModel($this->module);

        // Update Node's Siblings
        $node = $model->where('id = ?', $nodeId)->fetch();
        $affectNodes = $node[$selfField] === null
                ? $model->where("{$selfField} is null and {$sequenceField} > ?", $node[$sequenceField])
                : $model->where(
                    "{$selfField} = ? and {$sequenceField} > ?",
                    $node[$selfField], $node[$sequenceField]
                );
        foreach ($affectNodes as $affectNode)
        {
            $affectNode->update([
                $sequenceField => $affectNode[$sequenceField] - 1
            ]);
        }

        // Update Target's Children
        $target = $model->where('id = ?', $targetId)->fetch();
        $affectNodes = $model->where("{$selfField} = ?", $target['id']);
        foreach ($affectNodes as $affectNode)
        {
            $affectNode->update([
                $sequenceField => $affectNode[$sequenceField] + 1
            ]);
        }

        // Update Node
        $changes = [
            $selfField => $targetId
        ];
        if (isset($sequenceField))
        {
            $changes[$sequenceField] = 1;
        }
        $node = $model->where('id = ?', $nodeId)->fetch();
        $node->update($changes);

        $callback->onMoveInsideSuccess('Move successfully!');
    }

    /**
     * @throws
     */
    public function moveBefore($callback, $nodeId, $targetId, $selfField, $sequenceField)
    {
        $model = ModelPool::getModel($this->module);

        // Update Node's Siblings
        $node = $model->where('id = ?', $nodeId)->fetch();
        $affectNodes = !isset($selfField)
                ? $model->where("{$sequenceField} > ?", $node[$sequenceField])
                : (
                    $node[$selfField] === null
                        ? $model->where(
                            "{$selfField} is null and {$sequenceField} > ?",
                            $node[$sequenceField]
                        )
                        : $model->where(
                            "{$selfField} = ? and {$sequenceField} > ?",
                            $node[$selfField], $node[$sequenceField]
                        )
                );

        foreach ($affectNodes as $affectNode)
        {
            $affectNode->update([
                $sequenceField => $affectNode[$sequenceField] - 1
            ]);
        }

        // Update Target's Siblings
        $target = $model->where('id = ?', $targetId)->fetch();
        $affectNodes = !isset($selfField)
                ? $model->where("{$sequenceField} >= ?", $target[$sequenceField])
                : (
                    $target[$selfField] === null
                        ? $model->where(
                            "{$selfField} is null and {$sequenceField} >= ?",
                            $target[$sequenceField]
                        )
                        : $model->where(
                            "{$selfField} = ? and {$sequenceField} >= ?",
                            $target[$selfField], $target[$sequenceField]
                        )
                );
        foreach ($affectNodes as $affectNode)
        {
            $affectNode->update([
                $sequenceField => $affectNode[$sequenceField] + 1
            ]);
        }

        // Update Node
        $changes = [$sequenceField => $target[$sequenceField]];
        if (isset($selfField))
        {
            $changes[$selfField] = $target[$selfField];
        }
        $node = $model->where('id = ?', $nodeId)->fetch();
        $node->update($changes);

        $callback->onMoveBeforeSuccess('Move successfully!');
    }

    /**
     * @throws
     */
    public function moveAfter($callback, $nodeId, $targetId, $selfField, $sequenceField)
    {
        $model = ModelPool::getModel($this->module);

        // Update Node's Siblings
        $node = $model->where('id = ?', $nodeId)->fetch();
        $affectNodes = !isset($selfField)
                ? $model->where("{$sequenceField} > ?", $node[$sequenceField])
                : (
                    $node[$selfField] === null
                        ? $model->where("{$selfField} is null and {$sequenceField} > ?", $node[$sequenceField])
                        : $model->where(
                            "{$selfField} = ? and {$sequenceField} > ?",
                            $node[$selfField], $node[$sequenceField]
                        )
                );

        foreach ($affectNodes as $affectNode)
        {
            $affectNode->update([
                $sequenceField => $affectNode[$sequenceField] - 1
            ]);
        }

        // Update Target's Siblings
        $target = $model->where('id = ?', $targetId)->fetch();
        $affectNodes = !isset($selfField)
                ? $model->where("{$sequenceField} > ?", $target[$sequenceField])
                : (
                    $target[$selfField] === null
                        ? $model->where(
                            "{$selfField} is null and {$sequenceField} > ?",
                            $target[$sequenceField]
                        )
                        : $model->where(
                            "{$selfField} = ? and {$sequenceField} > ?",
                            $target[$selfField], $target[$sequenceField]
                        )
                );
        foreach ($affectNodes as $affectNode)
        {
            $affectNode->update([
                $sequenceField => $affectNode[$sequenceField] + 1
            ]);
        }

        // Update Node
        $target = $model->where('id = ?', $targetId)->fetch();
        $changes = [$sequenceField => $target[$sequenceField] + 1];
        if (isset($selfField))
        {
            $changes[$selfField] = $target[$selfField];
        }
        $node = $model->where('id = ?', $nodeId)->fetch();
        $node->update($changes);

        $callback->onMoveAfterSuccess('Move successfully!');
    }
}
