<?php
/**
 * @author AlexanderC
 */

namespace PStorage;

use PStorage\Helpers\DefinitionHelper;
use PStorage\Helpers\UniversalGS;
use PStorage\Model\Behaviors\ABehavior;
use PStorage\Model\Behaviors\Helpers\BehaviorApplyable;
use PStorage\Model\Comparators\Helpers\ComparatorApplyable;
use PStorage\Model\Comparators\Helpers\PredefinedComparators;
use PStorage\Model\Definition as ModelDefinition;
use PStorage\Model\Operations;
use PStorage\Model\TableDescriptorsConstants;
use PStorage\Model\Validator;
use PStorage\Storage\Table;
use PStorage\Storage\DefaultClient;
use PStorage\Storage\Client;

abstract class AModel
    extends UniversalGS
    implements Definition, TableDescriptorsConstants, PredefinedComparators
{
    use BehaviorApplyable;
    use ComparatorApplyable;
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
     * @var bool
     */
    private $usesComparatorApplyableTrait;

    /**
     * @var Storage\Table
     */
    private $table;

    /**
     * {@inherit}
     */
    public function __construct()
    {
        $this->definition = new ModelDefinition($this->definition());

        /** @var Client $client */
        $client = DefaultClient::getInstance()->getClient();

        $this->usesBehaviorApplyableTrait = in_array(
            "PStorage\\Model\\Behaviors\\Helpers\\BehaviorApplyable",
            class_uses(get_class())
        );

        $this->usesComparatorApplyableTrait = in_array(
            "PStorage\\Model\\Comparators\\Helpers\\ComparatorApplyable",
            class_uses(get_class())
        );

        // pre fill model properties
        if(func_num_args() > 0) {
            parent::__construct(func_get_arg(0));
        }

        if(true === $this->usesBehaviorApplyableTrait) {
            $this->importBehaviors();
        }

        if(true === $this->usesComparatorApplyableTrait) {
            $this->importComparators();
        }

        $this->table = new Table($this, $client->getStorage(), $client->getSerializer());
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
        } else {
            $validator->setModel($this);
        }

        return call_user_func_array([$validator, 'validate'], func_get_args());
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
    public function createCollectionFromArray(array & $data, $fetchMode = Collection::FETCH_LAZY)
    {
        $collectionData = [];
        $modelClass = get_class($this);

        foreach($data as & $modelData) {
            $collectionData[] = [
                'model' => $modelClass,
                'data' => $modelData
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