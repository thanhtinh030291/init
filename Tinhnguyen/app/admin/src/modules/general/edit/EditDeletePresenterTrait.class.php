<?php

namespace Lza\App\Admin\Modules\General\Edit;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait EditDeletePresenterTrait
{
    /**
     * @var boolean Is this action called from an ajax
     */
    private $isAjax;

    /**
     * Validate inputs and do Delete Record request
     *
     * @throws
     */
    public function doDelete($id, $isAjax)
    {
        $this->isAjax = $isAjax;
        $this->delete($this, $this->session->get('user.username'), $id);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onDeleteSuccess($data = null)
    {
        $this->createSitemap();
        if ($this->isAjax)
        {
            echo 'deleted';
            exit;
        }
        $this->viewer->navigateToModule();
    }
}
