<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage\Serialization\Driver;


use PStorage\Storage\Serialization\Exceptions\BrokenSerializedDataException;

class NativeDriver implements IDriver
{
    /**
     * @param mixed $data
     * @return string
     */
    public function serialize($data)
    {
        return serialize($data);
    }

    /**
     * @param string $string
     * @return mixed
     * @throws \PStorage\Storage\Serialization\Exceptions\BrokenSerializedDataException
     */
    public function unserialize($string)
    {
        $data = @unserialize($string);

        if(false === $data) {
            throw new BrokenSerializedDataException("Data provided for unserialization is broken");
        }

        return $data;
    }

}