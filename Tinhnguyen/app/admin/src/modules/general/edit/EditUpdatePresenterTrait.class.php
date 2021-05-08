<?php

namespace Lza\App\Admin\Modules\General\Edit;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait EditUpdatePresenterTrait
{
    /**
     * Validate inputs and do Update Records request
     *
     * @throws
     */
    public function doSave($item, $fields, $many)
    {
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
