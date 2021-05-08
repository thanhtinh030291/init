<?php

namespace Lza\App\Admin\Modules\General\Listall;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait ListallViewTrait
{
    /**
     * @var array List of Item IDs to be massive edited or deleted
     */
    private $ids;

    /**
     * @throws
     */
    protected function hasPermissionToAccess()
    {
        $this->data->table = $this->doGetTable($this->module);
        $this->data->permission = $this->permission->hasPermission(
            $this->session->get('user.username'),
            $this->data->table['id'],
            LIST_LEVEL
        );

        $settings = $this->data->table['settings'];
        $this->data->options = empty($settings) ? [] : $this->encryptor->jsonDecode($settings);

        return $this->data->permission !== false
                ? true : parent::hasPermissionToAccess();
    }

    /**
     * @throws
     */
    protected function loadViewTraitScript()
    {
        $region = $this->env->region;
        $module = $this->env->module;
        $view = $this->env->view;

        foreach ($this->data->fields as $field)
        {
            $id = $field['field'];
            if ($field['type'] === 'weakbelong')
            {
                $fieldParts = explode(':', $field['field']);
                $id = $fieldParts[0];
            }
            $this->session->add("js.{$region}.{$module}.{$view}", str_replace(
                '{$id}', $id,
                file_get_contents($this->getScriptPath() . "field/search/{$field['type']}.js")
            ));
        }
    }

    /**
     * @throws
     */
    public function getScriptPath()
    {
        return ADMIN_RES_PATH . "scripts/general/listall/";
    }

    /**
     * Event which is triggered if no action is performed
     *
     * @throws
     */
    protected function onNoActionActive()
    {
        $this->data->fields = $this->doGetTableFields($this->module);
    }

    /**
     * Event when Show All Ajax is called
     *
     * @throws
     */
    protected function onShowAll()
    {
        $this->doShowAll(
            $this->request->post,
            $this->data->regionPath,
            $this->data->table
        );
    }

    /**
     * Event when Massive Delete Button is clicked
     *
     * @throws
     */
    public function onBtnDeleteClick()
    {
        $this->ids = isset($this->request->post->rows) ? $this->request->post->rows : [];
        $this->doDeleteAll(array_unique($this->ids));
    }

    /**
     * Event when Massive Update Button is clicked
     *
     * @throws
     */
    public function onBtnUpdateClick()
    {
        $this->ids = isset($this->request->post->rows) ? $this->request->post->rows : [];
        $this->doUpdateAll(
            array_unique($this->ids),
            $this->data->massEditForm->getValues()
        );
    }
}
