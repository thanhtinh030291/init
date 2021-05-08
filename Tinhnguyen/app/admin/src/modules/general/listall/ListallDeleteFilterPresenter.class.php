<?php

namespace Lza\App\Admin\Modules\General\Listall;


/**
 * Handle Delete Filter action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ListallDeleteFilterPresenter extends ListallPresenter
{
    /**
     * Validate inputs and do Delete Filter request
     *
     * @throws
     */
    public function doDeleteFilter($id)
    {
        return $this->deleteFilter($this, $id);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onFilterDeletedSuccess($data = null)
    {
        $this->createSitemap();
        $this->viewer->navigateToModule();
    }
}
