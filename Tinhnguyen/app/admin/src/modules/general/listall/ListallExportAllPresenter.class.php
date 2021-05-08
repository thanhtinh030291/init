<?php

namespace Lza\App\Admin\Modules\General\Listall;


/**
 * Handle Export All Module Records action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ListallExportAllPresenter extends ListallPresenter
{
    /**
     * Validate inputs and do Export All Module Records request
     *
     * @throws
     */
    public function doExportAll($isExportAllVersions, $type, $fields)
    {
        $items = $isExportAllVersions
                ? $this->getListHistory($this)
                : $this->getList($this);
        return $this->export(
            $items, $fields, $type,
            $isExportAllVersions
        );
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onExportSuccess($data = null)
    {
        $this->createSitemap();
        $this->viewer->navigateToModule();
    }
}
