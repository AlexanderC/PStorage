<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


use PStorage\Storage\Drivers\IDriver;
use PStorage\Storage\Serialization\Driver\IDriver as SerializationIDriver;
use PStorage\Storage\Serialization\Factory as SerializationFactory;

class Client
{
    /**
     * @var Drivers\IDriver
     */
    protected $storage;

    /**
     * @var \PStorage\Storage\Serialization\Driver\IDriver
     */
    protected $serializer;

    /**
     * @param IDriver $storage
     * @param SerializationIDriver $serializer
     */
    public function __construct(IDriver $storage, SerializationIDriver $serializer = null)
    {
        $this->storage = $storage;
        $this->serializer = $serializer ? : SerializationFactory::getInstance()->create();
    }

    /**
     * @param \PStorage\Storage\Serialization\Driver\IDriver $serializer
     */
    public function setSerializer(SerializationIDriver $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return \PStorage\Storage\Serialization\Driver\IDriver
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param \PStorage\Storage\Drivers\IDriver $storage
     */
    public function setStorage(IDriver $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return \PStorage\Storage\Drivers\IDriver
     */
    public function getStorage()
    {
        return $this->storage;
    }
}