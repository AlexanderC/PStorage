<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Helpers;


class UniversalGS 
{
    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        if(!empty($properties)) {
            $this->filterInputProperties($properties);
            $this->properties = $properties;
        }
    }

    /**
     * This method is called every time is set an property
     *
     * @param array $values
     */
    protected function filterInputProperties(array & $values)
    {   }

    /**
     * @param scalar $name
     * @return bool
     */
    public function isProperty($name)
    {
        $this->assureScalar($name);

        return array_key_exists($name, $this->properties);
    }

    /**
     * @param scalar $name
     * @throws \OutOfRangeException
     */
    protected function assureScalar($name)
    {
        if(!is_scalar($name)) {
            throw new \OutOfRangeException("Assumed scalar given " . gettype($name) . " value");
        }
    }

    /**
     * @param scalar $name
     * @throws \OutOfBoundsException
     * @return mixed
     */
    public function __get($name)
    {
        $this->assureScalar($name);

        if(!$this->isProperty($name)) {
            throw new \OutOfBoundsException("Property {$name} does not exists");
        }

        return $this->properties[$name];
    }

    /**
     * @param scalar $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    public function __set($name, $value)
    {
        $this->assureScalar($name);

        $validate = [];
        $validate[$name] = $value;
        $this->filterInputProperties($validate);

        if(count($validate) !== 1) {
            throw new \InvalidArgumentException("Property {$name} is not allowed");
        }

        $this->properties[$name] = $value;
    }

    /**
     * @param string $method
     * @param array $values
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function __call($method, array $values)
    {
        if(preg_match("/^(get|set)(.+)$/ui", $method, $matches) && count($matches) === 3) {
            $name = lcfirst($matches[2]);

            return $this->$name;
        }

        throw new \OutOfBoundsException("Method {$method} does not exists");
    }
}