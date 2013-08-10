<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage\Serialization\Driver;


class MsgPackDriver 
{
    /**
     * @param mixed $data
     * @return string
     * @throws UnableToSerializeException
     */
    public function serialize($data)
    {
        $string = msgpack_pack($data);

        if(false === $string) {
            throw new UnableToSerializeException("Unable to serialize incoming data");
        }

        return $string;
    }

    /**
     * @param string $string
     * @return mixed
     * @throws BrokenSerializedDataException
     */
    public function unserialize($string)
    {
        $data = msgpack_unpack($string);

        if(false === $data) {
            throw new BrokenSerializedDataException("Data provided for unserialization is broken");
        }

        return $data;
    }
}