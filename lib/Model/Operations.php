<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Model;


trait Operations 
{
    /**
     * @param Client $client
     * @return void
     */
    public function save(Client $client = null)
    {
        $table = $this->table;

        if(null !== $client) {
            $table = clone $table;
            $table->setSerializer($client->getSerializer());
            $table->setStorage($client->getStorage());
        }

        $primaryKeyProperty = $this->getDefinition()->getPrimaryKeyProperty();

        if(self::DEFAULT_VALUE !== ($result = $this->$primaryKeyProperty)) {
            $table->save();
        } else {
            $this->$primaryKeyProperty = $table->add();
        }

        $this->postPersist();
    }

    /**
     * @param Client $client
     * @return void
     */
    public function delete(Client $client = null)
    {
        $table = $this->table;

        if(null !== $client) {
            $table = clone $table;
            $table->setSerializer($client->getSerializer());
            $table->setStorage($client->getStorage());
        }

        $table->delete();
    }

    /**
     * @param Client $client
     * @return Collection
     */
    public function findAll(Client $client = null)
    {
        $table = $this->table;

        if(null !== $client) {
            $table = clone $table;
            $table->setSerializer($client->getSerializer());
            $table->setStorage($client->getStorage());
        }

        return $table->findAll();
    }

    /**
     * @param Client $client
     * @return Collection
     */
    public function countAll(Client $client = null)
    {
        $table = $this->table;

        if(null !== $client) {
            $table = clone $table;
            $table->setSerializer($client->getSerializer());
            $table->setStorage($client->getStorage());
        }

        return $table->countAll();
    }

    /**
     * @param array $filters
     * @param Client $client
     * @return Collection
     */
    public function findBy(array $filters, Client $client = null)
    {
        $table = $this->table;

        if(null !== $client) {
            $table = clone $table;
            $table->setSerializer($client->getSerializer());
            $table->setStorage($client->getStorage());
        }

        return $table->findBy($filters);
    }

    /**
     * @param array $filters
     * @param Client $client
     * @return Collection
     */
    public function countBy(array $filters, Client $client = null)
    {
        $table = $this->table;

        if(null !== $client) {
            $table = clone $table;
            $table->setSerializer($client->getSerializer());
            $table->setStorage($client->getStorage());
        }

        return $table->countBy($filters);
    }

    /**
     * @param array $filters
     * @param Client $client
     * @return AModel|false
     */
    public function findOneBy(array $filters, Client $client = null)
    {
        $table = $this->table;

        if(null !== $client) {
            $table = clone $table;
            $table->setSerializer($client->getSerializer());
            $table->setStorage($client->getStorage());
        }

        return $table->findOneBy($filters);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param int $comparator
     * @param Client $client
     * @return Collection
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     * @throws \BadMethodCallException
     */
    public function findRangeByPrimaryKey(
        $offset, $limit, $comparator = Table::COMPARATOR_GREATER, Client $client = null
    ) {
        $table = $this->table;

        if(null !== $client) {
            $table = clone $table;
            $table->setSerializer($client->getSerializer());
            $table->setStorage($client->getStorage());
        }

        return $table->findRangeByPrimaryKey($offset, $limit, $comparator);
    }

    /**
     * @param string $method
     * @param array $values
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, array $values)
    {
        if(preg_match("/^(findBy|findOneBy|countBy)(.+)$/ui", $method, $matches) && count($matches) === 3) {
            $splitProperties = explode("and", str_ireplace("And", "and", $matches[2]));

            $filters = [];

            if(($filtersCount = count($splitProperties)) > ($valuesCount = count($values))
                || !in_array(abs($valuesCount - $filtersCount), [0, 1])) {
                throw new \BadMethodCallException("Mismatched number or properties with call values");
            } else if($valuesCount > $filtersCount && !($values[$valuesCount - 1] instanceof Client)) {
                throw new \BadMethodCallException("Invalid Client object provided");
            }

            foreach($splitProperties as $index => $property) {
                $filters[lcfirst($property)] = $values[$index];
            }

            if(stripos("findOneBy", $matches[1]) === 0) {
                $finderMethod = "findOneBy";
            } else if(stripos("findBy", $matches[1]) === 0) {
                $finderMethod = "findBy";
            } else if(stripos("countBy", $matches[1]) === 0) {
                $finderMethod = "countBy";
            }

            if($valuesCount > $filtersCount) {
                return call_user_func([$this, $finderMethod], $filters, $values[$valuesCount - 1]);
            } else {
                return call_user_func([$this, $finderMethod], $filters);
            }
        }

        return parent::__call($method, $values);
    }
}