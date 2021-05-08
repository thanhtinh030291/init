<?php

namespace Lza\App\Admin\Modules\General\Listall;


/**
 * Handle Export Selected Module Records action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ListallExportPresenter extends ListallPresenter
{
    /**
     * Validate inputs and do Export Selected Module Records request
     *
     * @throws
     */
    public function doExport($ids, $isExportAllVersions, $type, $fields)
    {
        $items = $isExportAllVersions
                ? $this->getListHistory($this, $ids)
                : $this->getList(
                    $this, null, null, null,
                    "id in (" . implode(',', $ids) . ")"
                );
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
