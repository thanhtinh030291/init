<?php

namespace Lza\LazyAdmin\Utility\Tool;


use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;

/**
 * Spout Handler handle Spout Spreadsheet Writer and Reader
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SpoutHandler implements SpreadsheetHandler
{
    /**
     * @var string Spreadsheet's type
     */
    private $type;

    /**
     * @var Box\Spout\Reader Library's object to read spreadsheets
     */
    private $reader;

    /**
     * @var Box\Spout\Writer Library;s object to write spreadsheets
     */
    private $writer;

    /**
     * @throws
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Lazy Loading Reader
     *
     * @throws
     */
    private function getReader()
    {
        $this->reader = $this->reader ?? ReaderFactory::create($this->type);
        return $this->reader;
    }

    /**
     * Lazy Loading Writer
     *
     * @throws
     */
    private function getWriter()
    {
        $this->writer = $this->writer ?? WriterFactory::create($this->type);
        return $this->writer;
    }

    /**
     * Read data from file
     *
     * @throws
     */
    public function read($fileName, $callback, array $sheetNames = [])
    {
        $this->getReader()->open($fileName);

        foreach ($this->getReader()->getSheetIterator() as $sheet)
        {
            $title = $sheet->getName();
            if (count($sheetNames) > 0 && !in_array($title, $sheetNames))
            {
                continue;
            }

            $rows = $sheet->getRowIterator();
            foreach ($rows as $rowNo => $columns)
            {
                $callback($rowNo, $columns);
            }
        }
    }

    /**
     * Write data to file
     *
     * @throws
     */
    public function openToBrowser($fileName)
    {
        $this->getWriter()->openToBrowser($fileName);
    }

    /**
     * Add row to file
     *
     * @throws
     */
    public function addRow($row)
    {
        $this->getWriter()->addRow($row);
    }

    /**
     * Close file
     *
     * @throws
     */
    public function close()
    {
        $this->getWriter()->close();
    }
}
