<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Helpers;


use PStorage\AModel;
use PStorage\Storage\Table;

class Paginator
{
    /**
     * @var int
     */
    protected $offset;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @var AModel
     */
    protected $model;

    /**
     * @var int
     */
    protected $comparator = Table::COMPARATOR_GREATER;

    /**
     * @param AModel $model
     * @param int $offset
     * @param int $perPage
     */
    public function __construct(AModel $model, $offset = 1, $perPage = 10)
    {
        $this->model = $model;
        $this->offset = abs((int) $offset);
        $this->perPage = abs((int) $perPage) ? : 10;
    }

    /**
     * @param int $comparator
     */
    public function setComparator($comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * @return int
     */
    public function getComparator()
    {
        return $this->comparator;
    }

    /**
     * @param AModel $model
     */
    public function setModel(AModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return \PStorage\Helpers\AModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = abs((int) $offset);
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $perPage
     */
    public function setPerPage($perPage)
    {
        $this->perPage = abs((int) $perPage) ? : 10;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        static $result;

        if(!$result) {
            $result = ceil($this->model->countAll() / $this->perPage);
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function hasToPaginate()
    {
        return $this->getTotal() > 0;
    }

    /**
     * @return \PStorage\Model\Collection
     */
    public function getEntries()
    {
        static $entries;

        if(!$entries) {
            $entries = $this->model->findRangeByPrimaryKey($this->offset, $this->perPage, $this->comparator);
        }

        return $entries;
    }

    /**
     * @return int
     */
    public function getNextPageOffset()
    {
        if($this->comparator == Table::COMPARATOR_GREATER) {
            return $this->getEntries()->last()->{$this->model->getDefinition()->getPrimaryKeyProperty()} + 1;
        } else {
            return $this->getEntries()->first()->{$this->model->getDefinition()->getPrimaryKeyProperty()};
        }
    }

    /**
     * @return int
     */
    public function getPreviousPageOffset()
    {
        if($this->comparator == Table::COMPARATOR_LESS) {
            return $this->getEntries()->last()->{$this->model->getDefinition()->getPrimaryKeyProperty()} + 1;
        } else {
            return $this->getEntries()->first()->{$this->model->getDefinition()->getPrimaryKeyProperty()} - 1;
        }
    }
}