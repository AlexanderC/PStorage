<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Model\Comparators;


abstract class AComparator
{
    const GREATER = 1;
    const LESS = -1;
    const EQUAL = 0;

    /**
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    abstract function compare($a, $b);
}