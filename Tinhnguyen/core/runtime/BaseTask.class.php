<?php

namespace Lza\LazyAdmin\Runtime;


/**
 * @var encryptor
 * @var mailer
 * @var setting
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
interface BaseTask
{
    function execute($echo = false);
}