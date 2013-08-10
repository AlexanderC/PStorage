<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage\Serialization;


use PStorage\Helpers\Singleton;

class Factory
{
    use Singleton;

    const DEFAULT_DRIVER = 'native';

    /**
     * @var string
     */
    protected $template;

    /**
     * {@inherit}
     */
    protected function __onAfterConstruct()
    {
        $this->template = __NAMESPACE__ . "\\Driver\\%sDriver";
    }

    /**
     * @param string $name
     * @return Driver\IDriver
     */
    public function create($name = self::DEFAULT_DRIVER)
    {
        $class = sprintf($this->template, ucfirst($name));
        return new $class;
    }
}