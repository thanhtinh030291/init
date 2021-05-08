<?php

namespace Lza\App\Admin\Modules;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BasePresenter;

/**
 * Default Presenter for Admin region
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class AdminPresenter extends BasePresenter
{
    use AdminPresenterTrait;

    /**
     * Do Get Menu Items request
     *
     * @throws
     */
    public function doGetMenu()
    {
        $moduleModel = ModelPool::getModel('lzamodule');
        $fieldModel = ModelPool::getModel('lzafield');
        $settingModel = ModelPool::getModel('lzasetting');
        $languageModel = ModelPool::getModel('lzalanguage');

        $result = [];
        if ($this->session->get('user.username') === null)
        {
            return $result;
        }

        if ($this->session->get('user.is_admin') === 'Yes')
        {
            $result = $this->getAdminMenu($moduleModel, $fieldModel, $settingModel, $languageModel);
        }
        else
        {
            $result = $this->getUserMenu($fieldModel, $settingModel, $languageModel);
        }

        return $result;
    }

    /**
     * @throws
     */
    protected function getUserMenu($fieldModel, $settingModel, $languageModel)
    {
        $result = [];

        $userModuleModel = ModelPool::getModel('user_module');
        $tables = $userModuleModel->where('username = ?', $this->session->get('user.username'));
        foreach ($tables as $table)
        {
            $moduleViews = [];
            if (($table['level'] & LIST_LEVEL) === LIST_LEVEL)
            {
                $moduleViews[] = "list";

                $datetimeFields = $fieldModel->where([
                    "lzafield.type" => [
                        "date",
                        "datetime",
                        "eventstart",
                        "eventend"
                    ],
                    "lzamodule.id" => $table['id']
                ]);
                if (count($datetimeFields) > 0)
                {
                    $moduleViews[] = "calendar";
                }

                $listFields = $fieldModel->where([
                    "lzafield.type" => [
                        "enum",
                        "belong",
                        "has",
                        "have"
                    ],
                    "lzamodule.id" => $table['id']
                ]);
                if (count($listFields) > 0)
                {
                    $moduleViews[] = "statistics";
                }
            }

            if (($table['level'] & ADD_LEVEL) === ADD_LEVEL)
            {
                $moduleViews[] = "add";
            }

            $table['views'] = $moduleViews;
            $table['children'] = [];
            $result[] = $table;
        }
        $this->data->userModules = $tables;

        return $result;
    }
}
