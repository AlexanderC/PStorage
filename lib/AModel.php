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
use PStorage\Model\Operations;
use PStorage\Model\Validator;
use PStorage\Storage\Table;
use PStorage\Storage\DefaultClient;
use PStorage\Storage\Client;

abstract class AModel extends UniversalGS implements Definition
{
    use BehaviorApplyable;
    use Operations;

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
     * @return bool
     */
    public function hasPrimaryKey()
    {
        return self::DEFAULT_VALUE !== $this->{$this->definition->getPrimaryKeyProperty()};
    }
}