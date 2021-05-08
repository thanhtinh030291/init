<?php

namespace Lza\App\Admin\Modules\General\Show;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait ShowPresenterTrait
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
            $this->session->add('alert_error', 'View Details is prohibited!');
        }
    }
}
