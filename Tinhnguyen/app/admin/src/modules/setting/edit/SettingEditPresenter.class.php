<?php

namespace Lza\App\Admin\Modules\Setting\Edit;


use Lza\App\Admin\Modules\AdminPresenter;
use Lza\Config\Models\ModelPool;

/**
 * Handle Edit Setting action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SettingEditPresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Get Settings request
     *
     * @throws
     */
    public function doGetFields($id, $columns = null, $conditions = null)
    {
        $model = ModelPool::getModel('lzasection');
        $sections = $model->where("id", $id);
        $sections->select("id `section`, title{$this->session->lzalanguage} `section_title`");
        $section = $sections->fetch();
        $this->data->section = $section;

        return $this->getFields($id, $columns, $conditions);
    }

    /**
     * Validate inputs and do Save Setting request
     *
     * @throws
     */
    public function doSave($items)
    {
        $this->save($this, $items);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onSaveSuccess($data = null)
    {
        $this->createSitemap();
        //$this->viewer->reload();
    }
}
