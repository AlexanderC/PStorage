<?php
/**
 * @author AlexanderC
 */

namespace PStorage;

use PStorage\Helpers\DefinitionHelper;
use PStorage\Helpers\UniversalGS;
use PStorage\Model\Definition as ModelDefinition;
use PStorage\Model\Validator;

abstract class AModel extends UniversalGS implements Definition
{
    const DEFAULT_VALUE = null;

    /**
     * @var Model\Definition
     */
    private $definition;

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
        foreach($values as $name => $value) {
            if(!in_array($name, $this->definition->getAllProperties())) {
                unset($values[$name]);
            }
        }
    }

    /**
     * @return bool
     */
    protected function validate()
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
    {   }

    /**
     * @return void
     */
    protected function postPersist()
    {   }
}