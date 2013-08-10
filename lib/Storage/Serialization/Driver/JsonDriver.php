<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage\Serialization\Driver;


use PStorage\Storage\Serialization\Exceptions\BrokenSerializedDataException;
use PStorage\Storage\Serialization\Exceptions\UnableToSerializeException;

class JsonDriver implements IDriver
{
    /**
     * @param mixed $data
     * @return string
     * @throws \PStorage\Storage\Serialization\Exceptions\UnableToSerializeException
     */
    public function serialize($data)
    {
        $string = json_encode($data);

        if(false === $string) {
            throw new UnableToSerializeException("Unable to serialize incoming data");
        }

        return $string;
    }

    /**
     * @param string $string
     * @return mixed
     * @throws \PStorage\Storage\Serialization\Exceptions\BrokenSerializedDataException
     */
    public function unserialize($string)
    {
        $data = json_decode($string, true);

        if(null === $data && ($error = json_last_error()) !== JSON_ERROR_NONE) {
            throw new BrokenSerializedDataException("Data provided for unserialization is broken, error {$error} thrown");
        }

        return $data;
    }
}