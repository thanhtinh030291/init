<?php

namespace Lza\App\Admin\Modules\General\Listall;


/**
 * Handle Import Module Records action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ListallImportPresenter extends ListallPresenter
{
    /**
     * Validate inputs and do Save Record request
     *
     * @throws
     */
    public function doSave($item, $many)
    {
        return $this->save(
            $this, $this->session->get('user.username'),
            $item, $many
        );
    }

    /**
     * Do Truncate Table request
     *
     * @throws
     */
    public function doTruncate()
    {
        return $this->truncate($this, $this->session->get('user.username'));
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onSaveSuccess()
    {
        // Do Nothing
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onTruncateSuccess()
    {
        // Do Nothing
    }
}
