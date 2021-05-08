<?php

namespace Lza\App\Admin\Modules\General\Show;


use Lza\App\Admin\Modules\AdminPresenter;

/**
 * Handle Show Module Record action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ShowPresenter extends AdminPresenter
{
    use ShowPresenterTrait;

    /**
     * Validate inputs and do Get Record History request
     *
     * @throws
     */
    public function doGetHistory($id)
    {
        return $this->getItemHistory($this, $id);
    }
}
