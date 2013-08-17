<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


use PStorage\AModel;

class Inflector
{
    /**
     * @param AModel $model
     * @return string
     */
    public static function modelToFolderName(AModel $model)
    {
        return str_replace("\\", "_", mb_strtolower(get_class($model)));
    }
}