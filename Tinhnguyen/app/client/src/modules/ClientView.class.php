<?php

namespace Lza\App\Client\Modules;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseView;

/**
 * Base View for Front End
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientView extends BaseView
{
    /**
     * @var array HTML Metadata
     */
    protected $metadata = [
        'robots' => 'all,index,follow',
        'revisit' => '15 days',
        'language' => 'English"',
        'rating' => 'General',
        'charset' => 'utf-8',
        'viewport' => 'user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, width=device-width'
    ];

    /**
     * The event when the page is creating
     *
     * @throws
     */
    protected function onCreate()
    {
        parent::onCreate();
        $this->setContentView("public/Blank.html");

        $this->uiOptions['config_dir'] = CLIENT_CONFIG_PATH;

        $this->getMetaData();

        $this->data->this = $this;
        $this->data->path = ROOT_FOLDER . chain_case(
            $this->view . (
                isset($this->env->child1) ? ("/{$this->env->child1}") : ''
            )
        );

        $email = $this->session->get('user.email');
        $this->data->isPcv = false !== strpos(
            $email !== null ? $email : '',
            '@pacificcross.com.vn'
        );

        $this->doGetMasterData();
        $this->data->metadata = $this->getMeta();

        $this->data->module = $this->module;
        $this->data->modulePath = $this->modulePath;
        $this->data->view = $this->view;

        $this->data->header = 'public/Header.html';

        $this->data->favicon = $this->setting->favicon;
        $this->data->logo = $this->setting->logo;
        $this->data->title = $this->getTitle();

        $this->data->fullname = $this->session->get('user.fullname');
        $this->data->role = $this->session->get('user.role');
        $this->data->isAdmin = $this->session->get('user.is_admin');
    }

    /**
     * Get meta data of the page
     *
     * @throws
     */
    protected function getMetaData()
    {
        $model = ModelPool::getModel('post');

        $post = $model->where('slug', chain_case($this->view))->fetch();
        if (!$post || $post['enabled'] === 0)
        {
            $this->isPageNotFound = true;
            return;
        }

        $this->metadata['description'] = $post['metadescription'];
        $this->metadata['keyword'] = $post['metakeyword'];
        $this->metadata['title'] = $post['metatitle'];
        $this->metadata['content'] = $post['content'];
        $this->metadata['enabled'] = $post['enabled'];
        $this->metadata['author'] = $this->setting->companyName;

        if (
            strpos($post['metatitle'], 'ref:') !== false ||
            strpos($post['metakeyword'], 'ref:') !== false ||
            strpos($post['metadescription'], 'ref:') !== false
        )
        {
            $refTable = str_replace('ref:', '', $post['metadescription']);
            $refItem = ModelPool::getModel($refTable)->where('id', $this->env->child1);
            $refItem = $refItem->select('metatitle,metakeyword,metadescription')->fetch();
            $this->metadata['description'] = $refItem['metadescription'];
            $this->metadata['keyword'] = $refItem['metakeyword'];
            $this->metadata['title'] = $refItem['metatitle'];
        }
    }

    /**
     * Event when Change Language Button is clicked
     *
     * @throws
     */
    public function onBtnChangeLanguageClick()
    {
        $this->session->lzalanguage = strcmp($this->request->language, '_en') === 0
                ? '' : $this->request->language;
        $this->refreshPage();
    }

    /**
     * Is caching enabled for this page?
     *
     * @throws
     */
    protected function useCaching()
    {
        return $this->setting->useCaching === 1;
    }

    /**
     * Is this page requires login?
     *
     * @throws
     */
    protected function isLoginRequired()
    {
        return true;
    }

    /**
     * Go to Login page
     *
     * @throws
     */
    public function navigateToLogin($return = true)
    {
        $this->navigateTo(WEBSITE_ROOT . "login");
    }

    /**
     * Get medadata
     *
     * @throws
     */
    public function getMeta()
    {
        return $this->metadata;
    }

    /**
     * Event when CSSes is loading
     *
     * @throws
     */
    protected function onLoadStyles()
    {
        parent::onLoadStyles();

        $this->data->styles['master'][] = CLIENT_RES_PATH . 'styles/bootstrap.min.css';
        $this->data->styles['master'][] = CLIENT_RES_PATH . 'styles/bootstrap.css';
        $this->data->styles['master'][] = CLIENT_RES_PATH . 'styles/share.css';
        $this->data->styles['master'][] = CLIENT_RES_PATH . 'styles/drawer.css';
    }

    /**
     * Event when Javascript is loading
     *
     * @throws
     */
    protected function onLoadScripts()
    {
        parent::onLoadScripts();

        $this->data->scripts['master'][] = CLIENT_RES_PATH . 'scripts/jquery-1.11.3.min.js';
        $this->data->scripts['master'][] = CLIENT_RES_PATH . 'scripts/bootstrap.min.js';
        $this->data->scripts['master'][] = CLIENT_RES_PATH . 'scripts/jquery.form.min-3.51.0-2014.06.20.js';
        $this->data->scripts['master'][] = CLIENT_RES_PATH . 'scripts/share_gain.js';
        $this->data->scripts['master'][] = CLIENT_RES_PATH . 'scripts/framework.js';
        $this->data->scripts['master'][] = CLIENT_RES_PATH . 'scripts/fields.js';
        $this->data->scripts['master'][] = CLIENT_RES_PATH . 'scripts/entities.js';
        $this->data->scripts['master'][] = CLIENT_RES_PATH . 'scripts/gain.js';
        $this->data->scripts['master'][] = CLIENT_RES_PATH . 'scripts/bootstrap.dropdown.js';
        $this->data->scripts['master'][] = CLIENT_RES_PATH . 'scripts/jquery.drawer.js';
        $this->data->scripts['master'][] = CLIENT_RES_PATH . 'scripts/hbs_loading.js';

    }
}
