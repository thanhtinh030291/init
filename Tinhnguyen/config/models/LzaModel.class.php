<?php

namespace Lza\Config\Models;


use Lza\Config\Models\BaseModel;

/**
 * Lza Model
 * Abstract class for all Lza Models
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
abstract class LzaModel extends BaseModel
{
    /**
     * @throws
     */
    public function getAllTableFields($module = null, $conditions = [])
    {
        // TODO: Implement later
        return [];
    }

    /**
     * @throws
     */
    public function getTableFields($module = null, $conditions = [])
    {
        $fields = $this->getAllTableFields($module);

        $filteredEntities = [];
        foreach ($fields as $field)
        {
            if (($field['level'] & $this->env->level) === $this->env->level)
            {
                $filteredEntities[] = $field;
            }
        }

        return $filteredEntities;
    }
}
