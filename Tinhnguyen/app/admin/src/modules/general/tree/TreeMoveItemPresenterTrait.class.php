<?php

namespace Lza\App\Admin\Modules\General\Tree;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait TreeMoveItemPresenterTrait
{
    /**
     * Validate inputs and do Move Tree Item request
     *
     * @throws
     */
    public function doMoveItem(
        $nodeId, $targetId, $position, $table, $fields
    )
    {
        $treeNodes = [];

        $sequenceField = null;
        foreach ($fields as $field)
        {
            if ($field['type'] === 'sequence')
            {
                $sequenceField = $field['field'];
                break;
            }
        }
        if (!isset($sequenceField))
        {
            return $treeNodes;
        }

        $selfField = null;
        foreach ($fields as $field)
        {
            if ($field['type'] === 'self')
            {
                $selfField = $field['field'];
                break;
            }
        }

        switch ($position)
        {
            case 'inside':
                if (!isset($selfField))
                {
                    $this->onError('Cannot move inside target node!');
                    return false;
                }
                $this->moveInside($this, $nodeId, $targetId, $selfField, $sequenceField);
                break;
            case 'before':
                if (!isset($sequenceField))
                {
                    $this->onError('Cannot move before target node!');
                    return false;
                }
                $this->moveBefore($this, $nodeId, $targetId, $selfField, $sequenceField);
                break;
            case 'after':
                if (!isset($sequenceField))
                {
                    $this->onError('Cannot move after target node!');
                    return false;
                }
                $this->moveAfter($this, $nodeId, $targetId, $selfField, $sequenceField);
                break;
            default:
                $this->onError('Move nothing!');
                return false;
        }
        return true;
    }
}
