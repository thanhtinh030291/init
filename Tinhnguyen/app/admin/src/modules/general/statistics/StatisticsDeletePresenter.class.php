<?php

namespace Lza\App\Admin\Modules\General\Statistics;


use Lza\App\Admin\Modules\AdminPresenter;

/**
 * Handle Delete Chart action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class StatisticsDeletePresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Delete Chart request
     *
     * @throws
     */
    public function doDelete($id)
    {
        return $this->deleteStatistic($this, $id);
    }
}
