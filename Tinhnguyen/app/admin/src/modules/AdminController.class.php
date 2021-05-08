<?php

namespace Lza\App\Admin\Modules;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseController;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class AdminController extends BaseController
{
    use AdminControllerTrait;

    /**
     * Insert Filter to database
     *
     * @throws
     */
    public function insertFilter($callback, $name, $userId, $moduleId, $columns, $conditions)
    {
        $model = ModelPool::getModel('lzafilter');

        $lastOrders = $model->where("user_id = ?", $userId);
        $lastOrders->select('max(order_by) as `order_by`');
        $lastOrder = $lastOrders->fetch();
        $order = $lastOrder['order_by'] === null ? 1 : ($lastOrder['order_by'] + 1);

        $item = $this->formatInputs([
            'name' => $name,
            'user_id' => $userId,
            'lzamodule_id' => $moduleId,
            'selections' => $this->encryptor->jsonEncode($columns),
            'conditions' => strlen($conditions) > 0 ? $conditions : 'true',
            'order_by' => $order
        ]);

        $result = $model->create(null, $item);
        if (!$result)
        {
            $callback->onError($this->i18n->failedToCreateItem);
            return false;
        }

        $callback->onFilterInsertedSuccess($result);
        return true;
    }

    /**
     * @throws
     */
    public function updateFilter($callback, $id, $name, $userId, $moduleId, $columns, $conditions)
    {
        $model = ModelPool::getModel('lzafilter');
        $original = $model->get($id)->fetch();

        $item = $this->formatInputs([
            "id" => $id,
            'name' => $name,
            'user_id' => $userId,
            'lzamodule_id' => $moduleId,
            'selections' => $this->encryptor->jsonEncode($columns),
            'conditions' => strlen($conditions) > 0 ? $conditions : 'true'
        ]);

        $changes = [];
        foreach ($item as $key => $value)
        {
            if ($original[$key] !== "{$value}")
            {
                $changes[$key] = $value;
            }
        }
        $result = $model->modify(null, $original, $changes);
        if (!$result)
        {
            $callback->onError($this->i18n->failedToUpdateItem);
            return false;
        }
        $callback->onFilterUpdatedSuccess($result);
        return true;
    }

    /**
     * @throws
     */
    public function deleteFilter($callback, $id)
    {
        $model = ModelPool::getModel('lzafilter');
        $item = $model->where('id = ?', $id)->fetch();

        $affectedItems = $model->where(
            "user_id = ? and
             lzamodule_id = ? and
             order_by > ?",
            $item['user_id'],
            $item['lzamodule_id'],
            $item['order_by']
        );
        foreach ($affectedItems as $affectedItem)
        {
            $affectedItem->update([
                "order_by" => ($affectedItem['order_by'] - 1)
            ]);
        }

        $result = $model->remove(null, $id);
        if (!$result)
        {
            $callback->onError($this->i18n->cannotDeleteItemId($id));
            return false;
        }

        $callback->onFilterDeletedSuccess($result);
        return true;
    }

    /**
     * Insert Statistic to database
     *
     * @throws
     */
    public function insertStatistic(
        $callback, $name, $userId, $moduleId, $fieldId, $conditions, $type, $extra, $width, $order
    )
    {
        $statisticModel = ModelPool::getModel('lzastatistic');
        $filferModel = ModelPool::getModel('lzafilter');

        $item = $this->formatInputs([
            'name' => $name,
            'user_id' => $userId,
            'lzamodule_id' => $moduleId,
            'lzafield_id' => $fieldId,
            'conditions' => strlen($conditions) > 0 ? $conditions : '',
            'type' => $type,
            'extra' => $extra,
            'width' => $width,
            'order_by' => $order
        ]);
        $affectedItems = $filferModel->where(
            implode(' and ', [
                'user_id = ?',
                'lzamodule_id = ?',
                'lzafield_id = ?',
                'order_by >= ?'
            ]),
            $userId,
            $moduleId,
            $fieldId,
            $order
        );
        foreach ($affectedItems as $affectedItem)
        {
            $affectedItem->update([
                "order_by" => ($affectedItem['order_by'] + 1)
            ]);
        }
        $result = $statisticModel->create(null, $item);
        if (!$result)
        {
            $callback->onError($this->i18n->failedToCreateItem);
            return false;
        }

        $callback->onSuccess($result);
        return true;
    }

    /**
     * Update Statistic to database
     *
     * @throws
     */
    public function updateStatistic(
        $callback, $id, $name, $userId, $moduleId, $fieldId, $conditions, $type, $extra, $width, $order
    )
    {
        $statisticModel = ModelPool::getModel('lzastatistic');
        $filferModel = ModelPool::getModel('lzafilter');

        $item = $this->formatInputs([
            "id" => $id,
            'name' => $name,
            'user_id' => $userId,
            'lzamodule_id' => $moduleId,
            'lzafield_id' => $fieldId,
            'conditions' => strlen($conditions) > 0 ? $conditions : '',
            'type' => $type,
            'extra' => $extra,
            'width' => $width,
            'order_by' => $order
        ]);
        $original = $statisticModel->get($id)->fetch();
        if ($item["order_by"] > $original['order_by'])
        {
            $affectedItems = $filferModel->where(
                implode(' and ', [
                    'user_id = ?',
                    'lzamodule_id = ?',
                    'lzafield_id = ?',
                    'order_by >= ?',
                    'order_by <= ?'
                ]),
                $userId,
                $moduleId,
                $fieldId,
                $original['order_by'],
                $item["order_by"]
            );
            foreach ($affectedItems as $affectedItem)
            {
                $affectedItem->update([
                    "order_by" => ($affectedItem['order_by'] - 1)
                ]);
            }
        }
        elseif ($item["order_by"] < $original['order_by'])
        {
            $affectedItems = $filferModel->where(
                implode(' and ', [
                    'user_id = ?',
                    'lzamodule_id = ?',
                    'lzafield_id = ?',
                    'order_by >= ?',
                    'order_by < ?'
                ]),
                $userId,
                $moduleId,
                $fieldId,
                $item["order_by"],
                $original['order_by']
            );
            foreach ($affectedItems as $affectedItem)
            {
                $affectedItem->update([
                    "order_by" => ($affectedItem['order_by'] + 1)
                ]);
            }
        }

        $changes = [];
        foreach ($item as $key => $value)
        {
            if ($original[$key] !== "{$value}")
            {
                $changes[$key] = $value;
            }
        }
        $result = $statisticModel->modify(null, $original, $changes);
        if (!$result)
        {
            $callback->onError($this->i18n->failedToUpdateItem);
            return false;
        }
        $callback->onSuccess($result);
        return true;
    }

    /**
     * @throws
     */
    public function deleteStatistic($callback, $id)
    {
        $statisticModel = ModelPool::getModel('lzastatistic');
        $filferModel = ModelPool::getModel('lzafilter');

        $item = $statisticModel->where('id = ?', $id)->fetch();

        $affectedItems = $filferModel->where(
            implode(' and ', [
                'user_id = ?',
                'lzamodule_id = ?',
                'lzafield_id = ?',
                'order_by >= ?'
            ]),
            $item['user_id'],
            $item['lzamodule_id'],
            $item['lzafield_id'],
            $item['order_by']
        );
        foreach ($affectedItems as $affectedItem)
        {
            $affectedItem->update([
                "order_by" => ($affectedItem['order_by'] - 1)
            ]);
        }

        $result = $statisticModel->remove(null, $id);
        if (!$result)
        {
            $callback->onError($this->i18n->cannotDeleteItemId($id));
            return false;
        }

        $callback->onSuccess($result);
        return true;
    }
}
