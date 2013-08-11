<?php
/**
 * @author AlexanderC
 */

namespace PStorage;

use PStorage\Helpers\DefinitionHelper;
use PStorage\Helpers\UniversalGS;
use PStorage\Model\Behaviors\ABehavior;
use PStorage\Model\Behaviors\Helpers\BehaviorApplyable;
use PStorage\Model\Definition as ModelDefinition;
use PStorage\Model\Validator;
use PStorage\Storage\Table;
use PStorage\Storage\DefaultClient;
use PStorage\Storage\Client;

abstract class AModel extends UniversalGS implements Definition
{
    use BehaviorApplyable;

    const DEFAULT_VALUE = null;

    /**
     * @var Model\Definition
     */
    private $definition;

    /**
     * @var bool
     */
    private $usesBehaviorApplyableTrait;

    /**
     * @var Storage\Table
     */
    private $table;

    /**
     * {@inherit}
     */
    public function __construct()
    {
        // pre fill model properties
        if(func_num_args() > 0) {
            parent::__construct(func_get_arg(0));
        }

        $this->definition = new ModelDefinition($this->definition());

        /** @var Client $client */
        $client = DefaultClient::getInstance()->getClient();

        $this->table = new Table($this, $client->getStorage(), $client->getSerializer());

        $this->usesBehaviorApplyableTrait = in_array(
            "PStorage\\Model\\Behaviors\\Helpers\\BehaviorApplyable",
            class_uses(get_class())
        );

        if(true === $this->usesBehaviorApplyableTrait) {
            $this->importBehaviors();
        }
    }

    /**
     * @return \PStorage\Storage\Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return ModelDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param array $values
     */
    protected function filterInputProperties(array & $values)
    {
        $properties = $this->definition->getAllProperties();
        $manyRelationProperties = $this->definition->getManyRelationProperties();

        foreach($values as $name => $value) {
            if(!in_array($name, $properties)) {
                unset($values[$name]);
            }


            if(in_array($name, $manyRelationProperties) && !is_array($value)) {
                unset($values[$name]);
            }
        }
    }

    /**
     * @return bool
     */
    public function validate()
    {
        static $validator;

        $this->prePersist();

        if(!($validator instanceof Validator)) {
            $validator = new Validator($this);
            return $validator->validate();
        } else {
            $validator->setModel($this);
            return $validator->validate();
        }
    }

    /**
     * @param scalar $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __get($name)
    {
        if(!in_array($name, $this->definition->getAllProperties())) {
            throw new \InvalidArgumentException("Property {$name} is not allowed");
        } else if(!$this->isProperty($name)) {
            return self::DEFAULT_VALUE;
        }

        return parent::__get($name);
    }

    /**
     * @return array
     */
    abstract protected function definition();

    /**
     * @return void
     */
    protected function prePersist()
    {
        if(true === $this->usesBehaviorApplyableTrait) {
            /** @var ABehavior $behavior */
            foreach($this->getPreBehaviors() as $behavior) {
                $behavior->apply($this);
            }
        }
    }

    /**
     * @return void
     */
    protected function postPersist()
    {
        if(true === $this->usesBehaviorApplyableTrait) {
            /** @var ABehavior $behavior */
            foreach($this->getPostBehaviors() as $behavior) {
                $behavior->apply($this);
            }
        }
    }

    /**
     * @param array $data
     * @param int $fetchMode
     * @return Collection
     */
    public static function createCollectionFromArray(array & $data, $fetchMode = Collection::FETCH_LAZY)
    {
        $collectionData = [];
        $modelClass = get_class();

        foreach($data as & $modelData) {
            $collectionData[] = [
                'model' => $modelClass,
                'date' => $modelData
            ];
        }

        return new Collection($collectionData, $fetchMode);
    }

    /**
     * @param Client $client
     * @return bool|int
     */
    public function save(Client $client = null)
    {
        $table = $this->table;

        if(null !== $client) {
            $table = clone $table;
            $table->setSerializer($client->getSerializer());
            $table->setStorage($client->getStorage());
        }

        if(self::DEFAULT_VALUE !== $this->id) {
            $result = $table->save();
        } else {
            $result = $table->add();
        }

        $this->postPersist();
        return $result;
    }

    /**
     * @param Client $client
     * @return bool
     */
    public function delete(Client $client = null)
    {
        $table = $this->table;

        if(null !== $client) {
            $table = clone $table;
            $table->setSerializer($client->getSerializer());
            $table->setStorage($client->getStorage());
        }

        return $table->delete();
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
     * @param string $method
     * @param array $values
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, array $values)
    {
        if(preg_match("/^(findBy|findOneBy)(.+)$/ui", $method, $matches) && count($matches) === 3) {
            $splitProperties = explode("and", str_ireplace("And", "and", $matches[2]));

            $filters = [];

            if(($filtersCount = count($splitProperties)) > ($valuesCount = count($values))
                || !in_array(abs($valuesCount - $filtersCount), [0, 1])) {
                throw new \BadMethodCallException("Mismatched number or properties with call values");
            } else if($valuesCount > $filtersCount && !($values[$valuesCount - 1] instanceof Client)) {
                throw new \BadMethodCallException("Invalid Client object provided");
            }

            foreach($splitProperties as $index => $property) {
                $filters[$property] = $values[$index];
            }

            $finderMethod = stripos("findOneBy", $matches[1]) === 0 ? "findOneBy" : "findBy";

            if($valuesCount > $filtersCount) {
                return call_user_func([$this, $finderMethod], $filters, $values[$valuesCount - 1]);
            } else {
                return call_user_func([$this, $finderMethod], $filters);
            }
        }

        return parent::__call($method, $values);
    }
}