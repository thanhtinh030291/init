<?php

use Lza\Config\Application;

$ds = DIRECTORY_SEPARATOR;
require_once dirname(dirname(__DIR__)) . "{$ds}lib{$ds}vendor{$ds}autoload.php";

Application::main();