<?php

namespace Lza\App\Admin;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Permission
 * Check User has permission to access a view
 *
 * @var session
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Permission
{
    use Singleton;

    /**
     * @throws
     */
    public function hasPermission($username, $moduleId, $level)
    {
        if ($this->session->get('user.is_admin') === 'Yes')
        {
            return LIST_LEVEL + SHOW_LEVEL + ADD_LEVEL + EDIT_LEVEL;
        }

        $model = ModelPool::getModel('user_permission');
        $permissions = $model->select("level")->where(
            "username = ? and module_id = ? and (level & ?) = ?",
            $username, $moduleId, $level, $level
        );
        $permission = $permissions->fetch();

        return $permission !== false ? $permission['level'] : false;
    }
}
