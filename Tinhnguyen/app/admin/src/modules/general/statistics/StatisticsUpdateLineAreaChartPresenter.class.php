<?php

namespace Lza\App\Admin\Modules\General\Statistics;


use Lza\App\Admin\Modules\AdminPresenter;
use Lza\Config\Models\ModelPool;

/**
 * Handle Update Line or Area Chart action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class StatisticsUpdateLineAreaChartPresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Update Line or Area Chart request
     *
     * @throws
     */
    public function doUpdateLineAreaChart($id, $name, $type, $extra, $width, $order)
    {
        $model = ModelPool::getModel('lzastatistic');
        $chart = $model->where('id = ?', $id)->fetch();
        return $this->updateStatistic(
            $this,
            $id, $name, $chart['user_id'], $chart['lzamodule_id'],
            $chart['lzafield_id'], '', $type, $extra, $width, $order
        );
    }
}
