<?php

namespace Lza\App\Admin\Elements;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait HistoryInput
{
    use AdminInput
    {
        onCreate as protected onAdminCreate;
    }

    /**
     * Event when the field is creating
     *
     * @throws
     */
    protected function onCreate($metadata, $item = null)
    {
        $this->onAdminCreate($metadata, $item);
        if (!empty($item))
        {
            $this->data->revision = $item['id'];
        }
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item = null)
    {
        if (!empty($item))
        {
            $this->setValue($item[$this->data->name]);
        }
    }

    /**
     * Event when the field value is pass to it's form
     *
     * @throws
     */
    protected function onSetFormValue()
    {
        // Do nothing
    }
}
