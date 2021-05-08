<?php

namespace Lza\App\Admin\Modules;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;
use Lza\LazyAdmin\Utility\Tool\Sitemap;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait AdminPresenterTrait
{
    /**
     * @throws
     */
    public function __construct()
    {
        parent::__construct();

        $model = ModelPool::getModel('lzalanguage');
        $this->data->languages = $model->order("order_by");
    }

    /**
     * @throws
     */
    protected function getAdminMenu($moduleModel, $fieldModel, $settingModel, $languageModel)
    {
        $result = [];

        $tables = $moduleModel->where("enabled = 'Yes' and parent IS NULL");
        $tables->order("order_by");
        foreach ($tables as $table)
        {
            $subTables = [];
            $settings = htmlspecialchars_decode($table['settings']);
            $options = empty($settings) ? [] : $this->encryptor->jsonDecode($settings);
            $children = $moduleModel->where("enabled = 'Yes' and parent = ?", $table['id']);
            $children->order("order_by");
            $datetimeTypes = [
                "date",
                "datetime",
                "eventstart",
                "eventend"
            ];
            $referenceTypes = [
                "enum",
                "belong",
                "has",
                "have"
            ];
            if (count($children) === 0)
            {
                $moduleViews = ["list"];

                $datetimeFields = $fieldModel->where([
                    "lzafield.type" => $datetimeTypes,
                    "lzamodule.id" => $table['id']
                ]);
                if (count($datetimeFields) > 0)
                {
                    $moduleViews[] = "calendar";
                }

                if (!isset($options->add) || $options->add)
                {
                    $moduleViews[] = "add";
                }

                $listFields = $fieldModel->where([
                    "lzafield.type" => $referenceTypes,
                    "lzamodule.id" => $table['id']
                ]);
                if (count($listFields) > 0)
                {
                    $moduleViews[] = "statistics";
                }

                $table['views'] = $moduleViews;
            }
            else
            {
                foreach ($children as $child)
                {
                    $childModuleViews = ["list"];
                    if (!isset($options->add) || $options->add)
                    {
                        $childModuleViews[] = "add";
                    }

                    $listFields = $fieldModel->where([
                        "lzafield.type" => $referenceTypes,
                        "lzamodule.id" => $child['id']
                    ]);
                    if (count($listFields) > 0)
                    {
                        $childModuleViews[] = "statistics";
                    }

                    $sequentialFields = $fieldModel->where([
                        "lzafield.type" => "sequence",
                        "lzamodule.id" => $child['id']
                    ]);
                    if (count($sequentialFields) > 0)
                    {
                        $childModuleViews[] = "tree";
                    }

                    $child['views'] = $childModuleViews;
                    $subTables[] = $child;
                }
            }
            $table['children'] = $subTables;
            $result[] = $table;
        }

        $settings = $settingModel->where("lzasection.id != 'hidden'");
        $settings->select("
            lzasetting.*,
            lzasection.id as section,
            lzasection.title{$this->session->lzalanguage} as section_title
        ");
        $settings->group("section");
        $settingTable = [
            'id' => 'setting',
            'icon' => 'gears',
            'views' => []
        ];

        foreach ($languageModel->select("code `code`") as $language)
        {
            $settingTable["single{$language['code']}"] = $this->i18n->setting;
            $settingTable["plural{$language['code']}"] = $this->i18n->settings;
        }
        foreach ($settings as $setting)
        {
            $settingTable['views'][] = [
                'uri' => $setting['section'],
                'text' => $setting['section_title']
            ];
        }
        $settingTable['children'] = [];
        $result[] = $settingTable;

        return $result;
    }

    /**
     * Forward Get Database Info request
     *
     * @throws
     */
    public function doGetDatabaseInfo()
    {
        return $this->getDatabaseInfo();
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onSuccess($data = null)
    {
        $this->createSitemap();
    }

    /**
     * Event when an action is failed to execute
     *
     * @throws
     */
    public function onError($message)
    {
        $this->logger->log(LogLevel::ERROR, $message);
    }

    /**
     * @throws
     */
    protected function createSitemap()
    {
        $sitemap = DIContainer::resolve(Sitemap::class, PROTOCOL . REQUESTED_HOST);
        $sitemap->setPath($_SERVER['DOCUMENT_ROOT'] . ROOT_FOLDER);
        $sitemap->setFilename('sitemap');

        $posts = ModelPool::getModel('Post')->select("
            slug `slug`,
            metatitle `title`,
            metakeyword `keyword`,
            metadescription `description`
        ");
        foreach ($posts as $post)
        {
            if (
                strpos(isset($post['title']) ? $post['title'] : '', 'ref:') !== false ||
                strpos(isset($post['keyword']) ? $post['keyword'] : '', 'ref:') !== false ||
                strpos(isset($post['description']) ? $post['description'] : '', 'ref:') !== false
            )
            {
                $refTable = str_replace('ref:', '', $post['description']);
                $refItems = ModelPool::getModel($refTable)->order("id");
                $refItems = $refItems->select("id");
                foreach ($refItems as $refItem)
                {
                    $sitemap->addItem(ROOT_FOLDER . "{$refTable}/{$refItem['id']}");
                }
            }
            else
            {
                $sitemap->addItem(ROOT_FOLDER . $post['slug']);
            }
        }
        $sitemap->createSitemapIndex(PROTOCOL . REQUESTED_HOST . '/', 'Today');
    }
}
