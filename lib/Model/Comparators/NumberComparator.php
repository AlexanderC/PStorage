<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Model\Comparators;


class NumberComparator extends AComparator
{
    /**
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    function compare($a, $b)
    {
        if($a < $b) {
            return self::LESS;
        } elseif($a > $b) {
            return self::GREATER;
        } else {
            return self::EQUAL;
        }
    }
}