<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Model\Behaviors;


use PStorage\AModel;

abstract class ABehavior
{
    const TYPE_PRE = 0x001;
    const TYPE_POST = 0x002;

    /**
     * @param AModel $model
     * @return void
     */
    abstract function apply(AModel $model);

    /**
     * @param array $configuration
     * @return void
     */
    abstract function init(array $configuration);

    /**
     * @return int
     */
    abstract static function getType();
}