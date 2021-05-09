<?php

namespace Lza\App\Admin\Modules\General\Statistics;


use Lza\App\Admin\Modules\AdminPresenter;
use Lza\Config\Models\ModelPool;

/**
 * Handle Update Pie Chart action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class StatisticsUpdatePieChartPresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Update Pie Chart request
     *
     * @throws
     */
    public function doUpdatePieChart($id, $name, $width, $order)
    {
        $model = ModelPool::getModel('lzastatistic');
        $chart = $model->where('id = ?', $id)->fetch();
        return $this->updateStatistic(
            $this,
            $id, $name, $chart['user_id'], $chart['lzamodule_id'],
            $chart['lzafield_id'], '', 'Pie Chart', '', $width, $order
        );
    }
}