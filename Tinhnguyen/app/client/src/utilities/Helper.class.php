<?php

namespace Lza\App\Client\Utilities;


/**
 * Help to process the direct billing requests
 *
 * @var controller
 * @var i18n
 * @var session
 * @var sql
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
interface Helper
{
    /**
     * Retrieve all pending requests
     */
    function getPendingRequests($langCode, $mbrNo, $effDate, $expDate);
}
