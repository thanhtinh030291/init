<?php

namespace Lza\LazyAdmin\Utility\Tool;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
interface SmsHandler
{
    function sendSms($receiver, $message);
}
