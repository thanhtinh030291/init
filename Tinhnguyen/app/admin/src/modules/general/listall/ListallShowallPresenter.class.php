<?php

namespace Lza\App\Admin\Modules\General\Listall;


use Lza\Config\Models\ModelPool;

/**
 * Handle Get All Module Records action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ListallShowallPresenter extends ListallPresenter
{
    use ListallShowallPresenterTrait;

    /**
     * @var array List of table settings
     */
    protected $options = [];

    /**
     * @var array Users' Table Filter
     */
    protected $filter;

    /**
     * @var array Users' Table Filter's parameters
     */
    protected $filterParameters;

    /**
     * @throws
     */
    private function getTableFields($table)
    {
        $level = $this->env->level;

        $filter = $this->session->get("user.user_filter.{$table}");
        if ($filter !== null)
        {
            $filterModel = ModelPool::getModel('lzafilter');
            $filters = $filterModel->where('id', $filter);
            $this->filter = $filters->fetch();

            $fieldModel = ModelPool::getModel('lzafield');
            $this->fields = $fieldModel->where(
                "lzafield.id", $this->encryptor->jsonDecode($this->filter['selections'], true)
            );
        }
        else
        {
            $fieldModel = ModelPool::getModel('lzafield');
            $this->fields = $fieldModel->where(
                "lzamodule.id = ? and
                 level & ? = ?",
                $table,
                $level, $level
            );
        }
        if (count($this->fields) === 0)
        {
            $module = $this->env->module;
            $model = ModelPool::getModel($module);
            $this->fields = $model->getTableFields($module);
        }
        else
        {
            $this->fields->select("
                lzamodule.id `table`,
                lzafield.field `field`,
                lzafield.id `id`,
                lzafield.type `type`,
                lzafield.mandatory `mandatory`,
                lzafield.order_by `order`,
                lzafield.level `level`,
                lzafield.statistic `statistic`,
                lzafield.display `display`
            ");
            $this->fields->order("lzafield.order_by");
        }
    }
}
