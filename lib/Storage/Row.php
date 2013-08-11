<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


class Row extends ATableSubItem
{
    const ROW_CONTENT_FILE_TPL = "%s_rc";

    /**
     * @return string|void
     * @throws \BadMethodCallException
     */
    public function getMainFolder()
    {
        throw new \BadMethodCallException("Main folder name should be retrieved via PrimaryKey object");
    }

    /**
     * @param string $rowContentFolder
     * @param string $tmpName
     * @return string
     */
    public function getRowContentFile($rowContentFolder, $tmpName)
    {
        return sprintf("%s/%s", $rowContentFolder, sprintf(self::ROW_CONTENT_FILE_TPL, $tmpName));
    }
}