<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Model;


use PStorage\AModel;
use PStorage\Model\Exceptions\PropertyRequiredException;
use PStorage\Model\Exceptions\UniqueConstrainFailException;

class Validator
{
    /**
     * @var \PStorage\AModel
     */
    protected $model;

    /**
     * @param AModel $model
     */
    public function __construct(AModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return AModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param \PStorage\AModel $model
     */
    public function setModel(AModel $model)
    {
        $this->model = $model;
    }

    /**
     * @throws Exceptions\UniqueConstrainFailException
     * @throws Exceptions\PropertyRequiredException
     * @return void
     */
    public function validate()
    {
        $definition = $this->model->getDefinition();
        $required = $definition->getRequiredProperties();
        $unique = $definition->getUniqueProperties();
        $primaryKey = $definition->getPrimaryKeyProperty();

        foreach($definition->getAllProperties() as $property) {
            if(in_array($property, $required)) {
                if(AModel::DEFAULT_VALUE === $this->model->$property) {
                    throw new PropertyRequiredException("Property {$property} is required");
                }
            }
        }

        foreach($unique as $property) {
            $locatorMethod = "findOneBy{$property}";

            if(false !== ($foundModel = call_user_func([$this->model, $locatorMethod], $this->model->$property))) {
                $primaryKeyFound = $foundModel->$primaryKey;

                throw new UniqueConstrainFailException(
                    "Property {$property} should be unique. Duplicate on #{$primaryKeyFound}"
                );
            }
        }
    }
}