<?php

namespace Lza\LazyAdmin\Utility\Text;


/**
 * Query stores query parts
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Query
{
    /**
     * @var array List of Database Query statements
     */
    public $query = null;

    /**
     * @throws
     */
    public function __construct($path)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->query = new Property(
            "{$path}query.txt",
            dirname(dirname(dirname(__DIR__))) . "{$ds}config{$ds}assets{$ds}query.txt"
        );
    }
}
