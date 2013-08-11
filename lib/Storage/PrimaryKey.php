<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


use PStorage\Storage\Exceptions\AtomarityViolationException;
use PStorage\Storage\Exceptions\PrimaryKeyIncrementalFileViolation;

class PrimaryKey extends ATableSubItem
{
    const MAIN_FOLDER = "pkvf";
    const PRIMARY_KEY_FILE_TPL = "%d.%d_pk";
    const DEFAULT_CHUNK_SIZE = 100;
    const INCREMENTAL_FILE = 'incpkv';
    const ROW_CONTENT_FOLDER_TPL = "%d.%d_rcf";

    /**
     * @var int
     */
    protected $chunkSize = self::DEFAULT_CHUNK_SIZE;

    /**
     * @param int $chunkSize
     */
    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = (int) $chunkSize;
    }

    /**
     * @return int
     */
    public function getChunkSize()
    {
        return $this->chunkSize;
    }

    /**
     * @return string
     */
    public function getMainFolder()
    {
        return sprintf("%s/%s", $this->table->getMainFolder(), self::MAIN_FOLDER);
    }

    /**
     * @return string
     */
    public function getIncrementalFile()
    {
        return sprintf("%s/%s", $this->getMainFolder(), self::INCREMENTAL_FILE);
    }

    /**
     * @param int $index
     * @return string
     */
    public function getIndexFile($index)
    {
        $min = floor($index / ($this->chunkSize + 1));

        $rangeStart = ($this->chunkSize + 1) * $min;
        $rangeEnd = $rangeStart + $this->chunkSize;

        return sprintf("%s/%s", $this->getMainFolder(), sprintf(self::PRIMARY_KEY_FILE_TPL, $rangeStart, $rangeEnd));
    }

    /**
     * @return string
     */
    public function getIndexFilesGlobPattern()
    {
        return sprintf("%s/%s", $this->getMainFolder(), str_replace("%d", "*", self::PRIMARY_KEY_FILE_TPL));
    }

    /**
     * @param int $index
     * @return string
     */
    public function getRowContentFolder($index)
    {
        $min = floor($index / ($this->chunkSize + 1));

        $rangeStart = ($this->chunkSize + 1) * $min;
        $rangeEnd = $rangeStart + $this->chunkSize;

        return sprintf("%s/%s", $this->getMainFolder(), sprintf(self::ROW_CONTENT_FOLDER_TPL, $rangeStart, $rangeEnd));
    }

    /**
     * Note: i do not know why increment is on this level, but it's
     *          a good occasion to increment it coz do not know whether
     *          it is used or not for storing a new row
     *
     * @return int
     * @throws Exceptions\PrimaryKeyIncrementalFileViolation
     * @throws Exceptions\AtomarityViolationException
     */
    public function getIncrementalValue()
    {
        $storage = $this->table->getStorage();

        $index = (int) $storage->read($this->getIncrementalFile());

        if($index <= 0) {
            throw new PrimaryKeyIncrementalFileViolation("Incremental file broken, please fix it manually");
        }

        if(!$storage->write($this->getIncrementalFile(), $index + 1)) {
            throw new AtomarityViolationException("Unable to persist new index into incremental file");
        }

        return $index;
    }
}