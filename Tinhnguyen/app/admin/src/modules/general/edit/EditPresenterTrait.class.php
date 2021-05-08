<?php

namespace Lza\App\Admin\Modules\General\Edit;


use Lza\LazyAdmin\Exception\DatabaseException;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait EditPresenterTrait
{
    /**
     * Validate inputs and do Get Record Details request
     *
     * @throws
     */
    public function doGetItem($id)
    {
        try
        {
            $this->data->prohibit = false;
            return $this->get($this, $id);
        }
        catch(DatabaseException $e)
        {
            $this->data->prohibit = true;
            $this->session->add('alert_error', 'Edit is prohibited!');
        }
    }

    /**
     * Event when an action is failed to execute
     *
     * @throws
     */
    public function onError($message)
    {
        parent::onError($message);
        if (!isset($this->request->action))
        {
            $this->viewer->setContentView(
                'general/edit/Error.html'
            );
        }
    }
}
