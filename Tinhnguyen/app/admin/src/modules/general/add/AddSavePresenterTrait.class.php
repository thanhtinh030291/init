<?php

namespace Lza\App\Admin\Modules\General\Add;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait AddSavePresenterTrait
{
    /**
     * Validate inputs and do Add Record request
     *
     * @throws
     */
    public function doSave($item, $fields, $many)
    {
        unset($item["id"]);

        foreach ($fields as $field)
        {
            if (in_array($field['type'], ['have', 'has']))
            {
                unset($item[$field['field']]);
            }
        }

        return $this->save(
            $this, $this->session->get('user.username'),
            $item, $many
        );
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onSaveSuccess($id = null)
    {
        $this->createSitemap();
        $this->viewer->navigateToShowItem($id);
    }
}
