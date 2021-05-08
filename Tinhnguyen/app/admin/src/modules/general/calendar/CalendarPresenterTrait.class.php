<?php

namespace Lza\App\Admin\Modules\General\Calendar;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait CalendarPresenterTrait
{
    /**
     * Validate inputs and do Get Record Details request
     *
     * @throws
     */
    public function doGetItem($id)
    {
        echo $this->encryptor->jsonEncode($this->get($this, $id));
    }
}
