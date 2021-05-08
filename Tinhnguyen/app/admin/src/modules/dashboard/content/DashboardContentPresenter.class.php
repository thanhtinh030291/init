<?php

namespace Lza\App\Admin\Modules\Dashboard\Content;


use Lza\App\Admin\Modules\General\Statistics\StatisticsPresenter;
use Lza\Config\Models\ModelPool;

/**
 * Handle Get Dashboard Contents action
 *
 * @var sql
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class DashboardContentPresenter extends StatisticsPresenter
{
    /**
     * Do Get Total Items request
     *
     * @throws
     */
    public function doGetItemCounts()
    {
        $this->data->itemCounts = [
            'Created' => 0,
            'Updated' => 0,
            'Deleted' => 0
        ];

        $sqls = [];
        $model = ModelPool::getModel('lzamodule');
        $modules = $model->where(
            'enabled = ? and
             db_id = ?',
            'Yes',
            'main'
        );
        $modules->select('id, single');
        foreach ($modules as $module)
        {
            $sqls[] = $this->sql->moduleItemCount([
                'id' => $module['id'],
                'single' => str_replace("'", "''", $module['single'])
            ]);
        }
        $sql = implode("\nUNION\n", $sqls);
        $sql = "SELECT action, SUM(count) count FROM ({$sql}) A GROUP BY action";

        $result = $this->sql->query($sql);
        if (is_array($result))
        {
            foreach ($result as $item)
            {
                $this->data->itemCounts[$item['action']] = $item['count'];
            }
        }
    }
}
