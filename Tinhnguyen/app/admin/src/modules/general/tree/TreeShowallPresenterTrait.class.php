<?php

namespace Lza\App\Admin\Modules\General\Tree;


use Lza\Config\Models\ModelPool;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait TreeShowallPresenterTrait
{
    private $node = '<div class="btn btn-outline btn-default" data-toggle="popover" data-content="%s">%s</div>';
    private $details = '<table class="table" style="margin-bottom: 0px">%s</table>';

    /**
     * Validate inputs and do Get Tree Data request
     *
     * @throws
     */
    public function doShowAll($node, $table, $fields)
    {
        $treeNodes = [];
        $model = ModelPool::getModel($this->module);

        $sequenceField = null;
        foreach ($fields as $field)
        {
            if ($field['type'] === 'sequence')
            {
                $sequenceField = $field['field'];
                break;
            }
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

        $conditions = true;
        $parameters = [];
        if (isset($selfField))
        {
            $conditions = "{$selfField} is null";
            if (isset($node))
            {
                $conditions = "{$selfField} = ?";
                $parameters[] = $node;
            }
        }

        $data = call_user_func_array([$model, 'where'], array_merge([$conditions], $parameters));
        if (isset($sequenceField))
        {
            $data->order($sequenceField);
        }

        $displayFields = explode(',', $table['display']);
        if (count($data))
        {
            foreach ($data as $item)
            {
                $displayLabels = [];
                foreach ($displayFields as $displayField)
                {
                    $displayLabels[] = $item[$displayField];
                }
                $treeNodes[] = $this->getTreeItem(
                    $item, $fields, implode(' - ', $displayLabels),
                    isset($sequenceField), isset($selfField)
                );
            }
        }

        echo $this->encryptor->jsonEncode($treeNodes);
        exit;
    }

    /**
     * @throws
     */
    private function getTreeItem($item, $fields, $name, $canMove = true, $canMoveTo = true)
    {
        $details = sprintf($this->details, $this->getItemInfo($fields, $item));
        $details = htmlspecialchars($details, ENT_QUOTES,'UTF-8');
        return [
            'id' => $item['id'],
            'name' => sprintf($this->node, $details, $name),
            'can_move' => boolval($canMove),
            'can_move_to' => boolval($canMoveTo),
            'load_on_demand' => true
        ];
    }

    /**
     * @throws
     */
    private function getItemInfo($fields, $item)
    {
        $rows = [];
        $listFields = ['has', 'have', 'belong', 'self', 'level', 'sequence'];
        foreach ($fields as $field)
        {
            if (!in_array($field['type'], $listFields))
            {
                if (!isset($item[$field['field']]) || !strlen($item[$field['field']]))
                {
                    $item[$field['field']] = '&nbsp;';
                }
                $rows[] = "
                    <tr>
                        <td style=\"text-align: right\">
                            {$field["single{$this->session->lzalanguage}"]}:</td>
                        <td><strong>{$item[$field['field']]}</strong></td>
                    </tr>
                ";
            }
        }
        //$rows[] = "<tr><td align=\"center\"><a href=\"\"><i class=\"fa fa-list\"></i></a><td align=\"center\"><a href=\"\"><i class=\"fa fa-edit\"></i></a></td></tr>";
        return '<thead>' . implode('', $rows) . '</thead>';
    }
}
