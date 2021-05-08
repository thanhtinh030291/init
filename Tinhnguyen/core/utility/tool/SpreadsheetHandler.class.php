<?php

namespace Lza\LazyAdmin\Utility\Tool;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
interface SpreadsheetHandler
{
    function read($fileName, $callBack, array $sheetNames = []);
    function openToBrowser($fileName);
    function addRow($row);
    function close();
}
