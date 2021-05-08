<?php

namespace Lza\LazyAdmin\Utility\Tool;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
interface NotificationHandler
{
    function sendToDevices($devices, $subject, $message, $data = [], $icon = null, $color = null, $badge = false);
    function sendToTopic($topics, $subject, $message, $data = [], $icon = null, $color = null, $badge = false);
    function sendToGroupTopic($topics, $subject, $message, $data = [], $icon = null, $color = null, $badge = false);
}
