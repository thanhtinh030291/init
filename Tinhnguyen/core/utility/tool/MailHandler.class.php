<?php

namespace Lza\LazyAdmin\Utility\Tool;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
interface MailHandler
{
    function sendEmail($sender, $receivers, $subject, $message, $isHtml = false);
}
