<?php
/**
 * @author AlexanderC
 */

namespace PStorage;


use PStorage\Helpers\CollectionInterface;

class Collection implements CollectionInterface
{
    const FETCH_LAZY = 0x001;
    const FETCH_EAGER = 0x002;

    /**
     * @var int
     */
    protected $fetchMode;

    /**
     * @var array
     */
    protected $collection = [];

    /**
     * @param array $models
     * @param int $fetchMode
     * @throws \BadMethodCallException
     * @throws \RuntimeException
     */
    public function __construct(array & $models = [], $fetchMode = self::FETCH_LAZY)
    {
        if(!in_array($fetchMode, [self::FETCH_LAZY, self::FETCH_EAGER])) {
            throw new \BadMethodCallException("Collection fetch mode should be both FETCH_LAZY or FETCH_EAGER");
        }

        $this->fetchMode = $fetchMode;

        foreach($models as $model) {
            if($model instanceof AModel) {
                $this->collection[] = $model;
            } else if(is_array($model)) {
                if(self::FETCH_EAGER === $this->fetchMode) {
                    $this->collection[] = $this->createModelFromArray($model);
                } else {
                    $this->collection[] = $model;
                }
            } else {
                throw new \RuntimeException("Collection models items should be both AModel or array");
            }
        }
    }

    /**
     * @param array $data
     * @return AModel
     * @throws \RuntimeException
     */
    protected function createModelFromArray(array $data)
    {
        if(!isset($data['model'], $data['data'])) {
            throw new \RuntimeException("Model array should contain both model and data keys");
        }

        return new $data['model']($data['data']);
    }

    /**
     * @return int
     */
    public function getFetchMode()
    {
        return $this->fetchMode;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        $model = $this->collection[$offset];

        if(self::FETCH_LAZY === $this->fetchMode && !($model instanceof AModel)) {
            $this->collection[$offset] = $model = $this->createModelFromArray($model);
        }

        return $model;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->collection[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $offset = key($this->collection);
        $model = current($this->collection);

        if(self::FETCH_LAZY === $this->fetchMode && !($model instanceof AModel) && false !== $model) {
            $this->collection[$offset] = $model = $this->createModelFromArray($model);
        }

        return $model;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $offset = key($this->collection);
        $model = next($this->collection);

        if(self::FETCH_LAZY === $this->fetchMode && !($model instanceof AModel) && false !== $model) {
            $this->collection[$offset] = $model = $this->createModelFromArray($model);
        }

        return $model;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->collection);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return false !== current($this->collection);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->collection);
    }

    /**
     * @return mixed
     */
    public function last()
    {
        $model = end($this->collection);

        if(self::FETCH_LAZY === $this->fetchMode && !($model instanceof AModel)) {
            $this->collection[key($this->collection)] = $model = $this->createModelFromArray($model);
        }

        return $model;
    }

    /**
     * @return mixed
     */
    public function first()
    {
        $model = reset($this->collection);

        if(self::FETCH_LAZY === $this->fetchMode && !($model instanceof AModel)) {
            $this->collection[key($this->collection)] = $model = $this->createModelFromArray($model);
        }

        return $model;
    }
}