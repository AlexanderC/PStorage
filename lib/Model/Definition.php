<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Model;


use PStorage\Helpers\DefinitionHelper;
use PStorage\Definition as MainDefinition;

class Definition implements MainDefinition
{
    use DefinitionHelper;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var array
     */
    protected $fields = [
        self::ONE => [],
        self::MANY => [],
        self::REQUIRED => [],
        self::UNIQUE => [],
        'list' => []
    ];

    /**
     * @param array $definition
     * @throws \RuntimeException
     */
    public function __construct(array $definition)
    {
        $this->validate($definition);

        foreach($definition as $propertyName => $flags) {
            if(self::isPrimaryKey($flags)) {
                $this->primaryKey = $propertyName;
            } else {
                if(self::isOneRelation($flags)) {
                    $this->fields[self::ONE][] = $propertyName;
                } elseif(self::isManyRelation($flags)) {
                    $this->fields[self::MANY][] = $propertyName;
                }

                if(self::isRequired($flags)) {
                    $this->fields[self::REQUIRED][] = $propertyName;
                }

                if(self::isUnique($flags)) {
                    $this->fields[self::UNIQUE][] = $propertyName;
                }
            }

            $this->fields['list'][] = $propertyName;
        }

        $this->fields[self::UNIQUE][] = $this->primaryKey;
    }

    /**
     * @param array $definition
     * @throws \RuntimeException
     */
    protected function validate(array & $definition)
    {
        $hasPrimaryKey = false;

        foreach($definition as $propertyName => $flags) {
            if(self::isPrimaryKey($flags)) {
                if(true === $hasPrimaryKey) {
                    throw new \RuntimeException("A model should not have more than one primary key");
                }

                $hasPrimaryKey = true;
            }
        }

        if(!$hasPrimaryKey) {
            throw new \RuntimeException("A model must have an primary key defined");
        }
    }

    /**
     * @return array
     */
    public function getOneRelationProperties()
    {
        return $this->fields[self::ONE];
    }

    /**
     * @return array
     */
    public function getManyRelationProperties()
    {
        return $this->fields[self::MANY];
    }

    /**
     * @return mixed
     */
    public function getRequiredProperties()
    {
        return $this->fields[self::REQUIRED];
    }

    /**
     * @return int|string
     */
    public function getPrimaryKeyProperty()
    {
        return $this->primaryKey;
    }

    /**
     * @return array
     */
    public function getAllProperties()
    {
        return $this->fields['list'];
    }

    /**
     * @return array
     */
    public function getAllPropertiesExceptPrimaryKey()
    {
        $all = $this->fields['list'];

        unset($all[array_search($this->primaryKey, $all)]);

        return array_values($all);
    }
}