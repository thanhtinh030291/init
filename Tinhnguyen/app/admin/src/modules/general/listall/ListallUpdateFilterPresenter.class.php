<?php

namespace Lza\App\Admin\Modules\General\Listall;


/**
 * Handle Update Filter action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ListallUpdateFilterPresenter extends ListallPresenter
{
    /**
     * Validate inputs and do Update Filter request
     *
     * @throws
     */
    public function doEditFilter(
        $id, $name, $userId, $moduleId, $columns, $conditions
    )
    {
        return $this->updateFilter(
            $this, $id, $name, $userId,
            $moduleId, $columns, $conditions
        );
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onFilterUpdatedSuccess($data = null)
    {
        $this->createSitemap();
        $this->viewer->navigateToModule();
    }
}
