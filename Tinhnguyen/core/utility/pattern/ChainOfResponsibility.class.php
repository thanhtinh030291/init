<?php

namespace Lza\LazyAdmin\Utility\Pattern;


/**
 * Chain of Responsibility design pattern
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
abstract class ChainOfResponsibility
{
    /**
     * @var object Next Node of the Chain
     */
    protected $nextResponsibility;

    /**
     * @throws
     */
    public function setNext($nextResponsibility)
    {
        $this->nextResponsibility = $nextResponsibility;
        return $nextResponsibility;
    }
}
