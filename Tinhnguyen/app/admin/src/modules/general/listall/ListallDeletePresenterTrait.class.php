<?php

namespace Lza\App\Admin\Modules\General\Listall;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait ListallDeletePresenterTrait
{
    /**
     * Validate inputs and do Massive Delete Records request
     *
     * @throws
     */
    public function doDeleteAll($ids)
    {
        return $this->deleteAll(
            $this, $this->session->get('user.username'), $ids
        );
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onDeleteAllSuccess($data = null)
    {
        $this->createSitemap();
        $this->viewer->navigateToModule();
    }
}
