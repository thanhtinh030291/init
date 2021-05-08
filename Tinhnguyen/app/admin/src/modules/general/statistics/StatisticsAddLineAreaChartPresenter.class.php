<?php

namespace Lza\App\Admin\Modules\General\Statistics;


use Lza\App\Admin\Modules\AdminPresenter;

/**
 * Handle Add Line or Area action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class StatisticsAddLineAreaChartPresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Add Line or Area Chart request
     *
     * @throws
     */
    public function doAddLineAreaChart(
        $name, $userId, $moduleId, $fieldId,
        $type, $extra, $width, $order
    )
    {
        return $this->insertStatistic(
            $this,
            $name, $userId, $moduleId, $fieldId,
            '', $type, $extra, $width, $order
        );
    }
}
