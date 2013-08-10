<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Helpers;

use PStorage\Definition;

trait DefinitionHelper
{
    /**
     * @param int $flags
     * @return int
     */
    protected static function isPrimaryKey($flags)
    {
        return (bool) ($flags & Definition::PK);
    }

    /**
     * @param int $flags
     * @return int
     */
    protected static function isOneRelation($flags)
    {
        return (bool) ($flags & Definition::ONE);
    }

    /**
     * @param int $flags
     * @return int
     */
    protected static function isManyRelation($flags)
    {
        return (bool) ($flags & Definition::MANY);
    }

    /**
     * @param int $flags
     * @return int
     */
    protected static function isRequired($flags)
    {
        return (bool) ($flags & Definition::REQUIRED);
    }

    /**
     * @param int $flags
     * @return int
     */
    protected static function isUnique($flags)
    {
        return (bool) ($flags & Definition::UNIQUE);
    }
}