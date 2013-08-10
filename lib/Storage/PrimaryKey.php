<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


class PrimaryKey extends ATableSubItem
{
    const MAIN_FOLDER = "pkvf";
    const PRIMARY_KEY_FILE_TPL = "%s_pk";
    const DEFAULT_CHUNK_SIZE = 100;

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
}