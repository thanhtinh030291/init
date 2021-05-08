<?php

namespace Lza\App\Admin\Modules\General\Tree;


use Lza\App\Admin\Modules\AdminView;

/**
 * Process Tree View page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class TreeView extends AdminView
{
    use TreeViewTrait;

    /**
     * Event when CSSes is loading
     *
     * @throws
     */
    protected function onLoadStyles()
    {
        parent::onLoadStyles();
        $this->data->styles['body'][] = WEBSITE_ROOT . 'libraries/mbraak/jqtree/jqtree.css';
        $this->data->styles['body'][] = WEBSITE_ROOT . 'admin-res/styles/tree.css';
    }
}
