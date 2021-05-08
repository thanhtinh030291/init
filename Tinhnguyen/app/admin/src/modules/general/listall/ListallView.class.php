<?php

namespace Lza\App\Admin\Modules\General\Listall;


use Lza\App\Admin\Modules\AdminView;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Process List page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ListallView extends AdminView
{
    use ListallViewTrait;

    /**
     * Event when the page is creating
     *
     * @throws
     */
    protected function onCreate()
    {
        if (isset($this->request->post->listFilter))
        {
            if ($this->request->post->listFilter === 0)
            {
                $this->session->remove("user.user_filter.{$this->env->module}");
            }
            else
            {
                $this->session->set(
                    "user.user_filter.{$this->env->module}",
                    $this->request->post->listFilter
                );
            }
        }
        parent::onCreate();

        if (!$this->data->table || $this->data->table['enabled'] !== 'Yes')
        {
            $view = $this->doGetView($this->module);
            if ($view !== false)
            {
                $link = str_replace(
                    chain_case($this->module),
                    chain_case($view['name']),
                    $_SERVER['REQUEST_URI']
                );
                header("location: {$link}");
                exit();
            }

            $this->setError('Module Not Found!');
            $this->setContentView('general/listall/Error.html');
            return;
        }

        $this->data->filters = $this->doGetFilters(
            $this->session->get('user.username'),
            $this->data->table['id']
        );
        $this->data->fields = $this->doGetTableFields($this->module);
        $this->data->allFields = $this->doGetAllTableFields($this->module);

        if (!$this->isAjax())
        {
            $settings = htmlspecialchars_decode($this->data->table['settings']);
            $options = empty($settings) ? [] : $this->encryptor->jsonDecode($settings, true);

            if (!isset($options['edit']) || $options['edit'] !== false)
            {
                $this->onCreateMassEditForm();
            }

            if (!isset($options['delete']) || $options['delete'] !== false)
            {
                $this->onCreateMassDeleteForm();
            }

            $this->onCreateImportForm();
        }
    }

    /**
     * Event when the Mass Edit From is creating
     *
     * @throws
     */
    protected function onCreateMassEditForm()
    {
        $this->data->massEditForm = DIContainer::resolve(
            MassEditForm::class, $this, "AdminMassEditForm"
        );
        $this->data->massEditForm->setInputs($this->data->fields);
    }

    /**
     * Event when the Mass Delete form is creating
     *
     * @throws
     */
    protected function onCreateMassDeleteForm()
    {
        $this->data->massDeleteForm = DIContainer::resolve(
            MassDeleteForm::class, $this, "AdminMassDeleteForm"
        );
        $this->data->massDeleteForm->onSubmit();
    }

    /**
     * Event when the Import form is creating
     *
     * @throws
     */
    protected function onCreateImportForm()
    {
        $this->data->importForm = DIContainer::resolve(ImportForm::class, $this, "AdminImportForm");
        $this->data->importForm->onSubmit($this->data->table, $this->data->allFields);
    }

    /**
     * Event when CSSes is loading
     *
     * @throws
     */
    protected function onLoadStyles()
    {
        parent::onLoadStyles();
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'libraries/datetimepicker/DatetimePicker/jquery.datetimepicker.css';
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'vendors/mistic100/jquery-querybuilder/dist/css/query-builder.default.min.css';
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'vendors/select2/select2/dist/css/select2.min.css';
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'admin-res/styles/select2-bootstrap.min.css';
        $this->loadViewStyles();
    }

    /**
     * @throws
     */
    protected function loadViewStyles()
    {
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'vendors/iron-summit-media/startbootstrap-sb-admin-2/'
                . 'bower_components/datatables/media/css/dataTables.bootstrap.css';
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'admin-res/styles/responsive.dataTables.min.css';
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'admin-res/styles/dataTables.checkboxes.css';
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'admin-res/styles/listall.css';
    }

    /**
     * Event when Javascript is loading
     *
     * @throws
     */
    protected function onLoadScripts()
    {
        parent::onLoadScripts();
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'vendors/mistic100/jquery-querybuilder/dist/js/query-builder.standalone.min.js';
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'vendors/mistic100/jquery-querybuilder/src/plugins/sql-parser/browser/sql-parser.min.js';
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'vendors/select2/select2/dist/js/select2.full.min.js';
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'admin-res/scripts/general/listall/listall.js';
        $this->loadViewScripts();
    }

    /**
     * @throws
     */
    protected function loadViewScripts()
    {
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'vendors/iron-summit-media/startbootstrap-sb-admin-2/bower_components/'
                . 'datatables/media/js/jquery.dataTables.min.js';
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'vendors/iron-summit-media/startbootstrap-sb-admin-2/bower_components/'
                . 'datatables/media/js/dataTables.bootstrap.js';
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'admin-res/scripts/dataTables.responsive.min.js';
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'admin-res/scripts/dataTables.checkboxes.min.js';
        $this->loadViewTraitScript();
    }

    /**
     * Event when Import button is clicked
     *
     * @throws
     */
    public function onBtnImportClick()
    {
        // Unused: Handled by Import Form
    }

    /**
     * Event when Export button is clicked
     *
     * @throws
     */
    public function onBtnExportClick()
    {
        $this->ids = isset($this->request->post->rows) ? $this->request->post->rows : [];
        $this->doExport(
            $this->ids, isset($this->request->post->exportExportAllVersion),
            $this->request->post->exportType, $this->data->allFields
        );
        exit();
    }

    /**
     * Event when Export All button is clicked
     *
     * @throws
     */
    public function onBtnExportAllClick()
    {
        $this->doExportAll(
            isset($this->request->post->exportAllExportAllVersion),
            $this->request->post->exportAllType,
            $this->data->allFields
        );
        exit();
    }

    /**
     * Event when Add Filter button is clicked
     *
     * @throws
     */
    public function onBtnAddFilterClick()
    {
        $this->doAddFilter(
            $this->request->post->addFilterName,
            $this->session->get('user.id'),
            $this->data->table['id'],
            $this->request->post->addFilterColumns,
            $this->request->post->addFilterConditions
        );
    }

    /**
     * Event when Update Filter button is clicked
     *
     * @throws
     */
    public function onBtnUpdateFilterClick()
    {
        $this->doEditFilter(
            $this->request->post->editFilterId,
            $this->request->post->editFilterName,
            $this->session->get('user.id'),
            $this->data->table['id'],
            $this->request->post->editFilterColumns,
            $this->request->post->editFilterConditions
        );
    }

    /**
     * Event when Delete Filter button is clicked
     *
     * @throws
     */
    public function onBtnDeleteFilterClick()
    {
        $isFilter = $this->session->get("user.user_filter.{$this->env->module}");
        if ($isFilter !== null && $isFilter === $this->request->post->filterItem)
        {
            $this->session->set("user.user_filter.{$this->env->module}");
        }
        $this->doDeleteFilter($this->request->post->filterItem);
    }
}
