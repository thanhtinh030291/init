<?php

namespace Lza\LazyAdmin\Utility\Tool;


use XMLWriter;

/**
 * Sitemap helps create sitemap for the website
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Sitemap
{
    const EXT = '.xml';
    const SCHEMA = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    const DEFAULT_PRIORITY = 0.5;
    const ITEM_PER_SITEMAP = 50000;
    const SEPERATOR = '-';
    const INDEX_SUFFIX = 'index';

    /**
     * @var XMLWriter Writer to write XML elements
     */
    private $writer;

    /**
     * @var string Current doamain name
     */
    private $domain;

    /**
     * @var string Real Path of the Website
     */
    private $path;

    /**
     * @var string File name to be written
     */
    private $filename = 'sitemap';

    /**
     * @var integer Current item's index
     */
    private $currentItem = 0;

    /**
     * @var integer Current Sitemap's index
     */
    private $currentSitemap = 0;

    /**
     * @throws
     */
    public function __construct($domain)
    {
        $this->setDomain($domain);
    }

    /**
     * @throws
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @throws
     */
    private function getDomain()
    {
        return $this->domain;
    }

    /**
     * @throws
     */
    private function getWriter()
    {
        return $this->writer;
    }

    /**
     * @throws
     */
    private function setWriter(\XMLWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @throws
     */
    private function getPath()
    {
        return $this->path;
    }

    /**
     * @throws
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @throws
     */
    private function getFilename()
    {
        return $this->filename;
    }

    /**
     * @throws
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @throws
     */
    private function getCurrentItem()
    {
        return $this->currentItem;
    }

    /**
     * @throws
     */
    private function increaseCurrentItem()
    {
        $this->currentItem = $this->currentItem + 1;
    }

    /**
     * @throws
     */
    private function getCurrentSitemap()
    {
        return $this->currentSitemap;
    }

    /**
     * @throws
     */
    private function incCurrentSitemap()
    {
        $this->currentSitemap = $this->currentSitemap + 1;
    }

    /**
     * @throws
     */
    private function startSitemap()
    {
        $this->setWriter(new XMLWriter());
        if ($this->getCurrentSitemap())
        {
            $this->getWriter()->openURI(
                $this->getPath() .
                $this->getFilename() .
                self::SEPERATOR .
                $this->getCurrentSitemap() .
                self::EXT
            );
        }
        else
        {
            $this->getWriter()->openURI($this->getPath() . $this->getFilename() . self::EXT);
        }
        $this->getWriter()->startDocument('1.0', 'UTF-8');
        $this->getWriter()->setIndent(true);
        $this->getWriter()->startElement('urlset');
        $this->getWriter()->writeAttribute('xmlns', self::SCHEMA);
    }

    /**
     * @throws
     */
    public function addItem($loc, $priority = self::DEFAULT_PRIORITY, $changefreq = NULL, $lastmod = NULL)
    {
        if (($this->getCurrentItem() % self::ITEM_PER_SITEMAP) === 0)
        {
            if ($this->getWriter() instanceof \XMLWriter)
            {
                $this->endSitemap();
            }
            $this->startSitemap();
            $this->incCurrentSitemap();
        }
        $this->increaseCurrentItem();
        $this->getWriter()->startElement('url');
        $this->getWriter()->writeElement('loc', $this->getDomain() . $loc);
        $this->getWriter()->writeElement('priority', $priority);
        if ($changefreq)
        {
            $this->getWriter()->writeElement('changefreq', $changefreq);
        }
        if ($lastmod)
        {
            $this->getWriter()->writeElement('lastmod', $this->getLastModifiedDate($lastmod));
        }
        $this->getWriter()->endElement();
        return $this;
    }

    /**
     * @throws
     */
    private function getLastModifiedDate($date)
    {
        if (ctype_digit($date))
        {
            return date('Y-m-d', $date);
        }
        else
        {
            $date = strtotime($date);
            return date('Y-m-d', $date);
        }
    }

    /**
     * @throws
     */
    private function endSitemap()
    {
        if (!$this->getWriter())
        {
            $this->startSitemap();
        }
        $this->getWriter()->endElement();
        $this->getWriter()->endDocument();
    }

    /**
     * @throws
     */
    public function createSitemapIndex($loc, $lastmod = 'Today')
    {
        $this->endSitemap();
        $indexwriter = new XMLWriter();
        $indexwriter->openURI(
            $this->getPath() .
            $this->getFilename() .
            self::SEPERATOR .
            self::INDEX_SUFFIX .
            self::EXT
        );
        $indexwriter->startDocument('1.0', 'UTF-8');
        $indexwriter->setIndent(true);
        $indexwriter->startElement('sitemapindex');
        $indexwriter->writeAttribute('xmlns', self::SCHEMA);
        for ($index = 0; $index < $this->getCurrentSitemap(); $index++)
        {
            $indexwriter->startElement('sitemap');
            $indexwriter->writeElement(
                'loc',
                $loc . $this->getFilename()
                    . ($index ? self::SEPERATOR . $index : '') . self::EXT
            );
            $indexwriter->writeElement(
                'lastmod', $this->getLastModifiedDate($lastmod)
            );
            $indexwriter->endElement();
        }
        $indexwriter->endElement();
        $indexwriter->endDocument();
    }
}
