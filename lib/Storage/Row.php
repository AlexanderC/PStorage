<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


class Row extends ATableSubItem
{
    const MAIN_FOLDER = "pkvf";
    const ROW_CONTENT_FILE_TPL = "%s_rcf";

    /**
     * @return string
     */
    public function getMainFolder()
    {
        return sprintf("%s/%s", $this->table->getMainFolder(), self::MAIN_FOLDER);
    }
}