<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Model;


use PStorage\AModel;

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
     * @return bool
     */
    public function validate()
    {
        foreach($this->model->getDefinition()->getAllProperties() as $property) {
            // TODO: validate all properties; do not forget unique values
        }
    }
}