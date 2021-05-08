<?php

namespace Lza\App\Admin\Modules\General\Listall;


/**
 * Handle Add New Filter action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ListallAddFilterPresenter extends ListallPresenter
{
    /**
     * @throws
     */
    public function doAddFilter($name, $userId, $moduleId, $columns, $conditions)
    {
        return $this->insertFilter(
            $this,
            $name, $userId, $moduleId,
            $columns, $conditions
        );
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onFilterInsertedSuccess($data = null)
    {
        $this->createSitemap();
        $this->viewer->navigateToModule();
    }
}
