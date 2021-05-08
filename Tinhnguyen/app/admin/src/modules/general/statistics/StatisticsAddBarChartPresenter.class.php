<?php

namespace Lza\App\Admin\Modules\General\Statistics;


use Lza\App\Admin\Modules\AdminPresenter;

/**
 * Handle Add Bar Chart action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class StatisticsAddBarChartPresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Add Bar Chart request
     *
     * @throws
     */
    public function doAddBarChart(
        $name, $userId, $moduleId, $fieldId,
        $type, $width, $order
    )
    {
        return $this->insertStatistic(
            $this,
            $name, $userId, $moduleId, $fieldId,
            '', $type, '', $width, $order
        );
    }
}
