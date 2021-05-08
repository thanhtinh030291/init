<?php

namespace Lza\App\Admin\Modules\General\Calendar;


use Lza\App\Admin\Modules\General\Listall\ListallView;

/**
 * Process Calendar page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class CalendarView extends ListallView
{
    use CalendarViewTrait;

    /**
     * @throws
     */
    protected function loadViewStyles()
    {
        $this->data->styles['master'][] = WEBSITE_ROOT . 'libraries/fullcalendar/fullcalendar.min.css';
        $this->data->styles['body'][] = WEBSITE_ROOT . 'admin-res/styles/listall.css';
        $this->data->styles['body'][] = WEBSITE_ROOT . 'admin-res/styles/calendar.css';
    }
}
