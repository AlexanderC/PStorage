<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Model\Comparators\Helpers;

use PStorage\Model\Comparators\AComparator;

trait ComparatorApplyable
{
    /**
     * @var array
     */
    protected $comparators = [];

    /**
     * @param string $property
     * @param AComparator $comparator
     */
    public function setComparator($property, AComparator $comparator)
    {
        $this->comparators[$property] = $comparator;
    }

    /**
     * @return array
     */
    public function getComparators()
    {
        return $this->comparators;
    }

    /**
     * @param string $property
     * @return bool
     */
    public function hasComparator($property)
    {
        return isset($this->comparators[$property]);
    }

    /**
     * @return void
     */
    protected function importComparators()
    {
        foreach($this->comparators() as $property => $comparator) {
            if(!($comparator instanceof AComparator)) {
                $comparatorClass = sprintf("PStorage\\Model\\Comparators\\%sComparator", ucfirst($comparator));
                $comparator = new $comparatorClass;
            }

            $this->setComparator($property, $comparator);
        }
    }

    /**
     * @return array
     */
    abstract protected function comparators();
}