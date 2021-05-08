<?php

namespace Lza\App\Admin\Modules\General\Listall;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait ListallUpdatePresenterTrait
{
    /**
     * Validate inputs and do Massive Update Records request
     *
     * @throws
     */
    public function doUpdateAll($ids, $item)
    {
        $fields = $this->getTableFields();
        foreach ($fields as $field)
        {
            if (in_array($field['type'], ['have', 'has']))
            {
                unset($item[$field['field']]);
            }
        }
        $this->updateAll(
            $this, $this->session->get('user.username'),
            $ids, $item
        );
        return true;
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onUpdateAllSuccess($data = null)
    {
        $this->createSitemap();
        $this->viewer->navigateToModule();
    }
}
