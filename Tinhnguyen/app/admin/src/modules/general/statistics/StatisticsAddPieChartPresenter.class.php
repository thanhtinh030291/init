<?php

namespace Lza\App\Admin\Modules\General\Statistics;


use Lza\App\Admin\Modules\AdminPresenter;

/**
 * Handle Add Pie Chart action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class StatisticsAddPieChartPresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Add Pie Chart request
     *
     * @throws
     */
    public function doAddPieChart(
        $name, $userId, $moduleId, $fieldId, $width, $order
    )
    {
        return $this->insertStatistic(
            $this,
            $name, $userId, $moduleId, $fieldId,
            '', 'Pie Chart', '', $width, $order
        );
    }
}
