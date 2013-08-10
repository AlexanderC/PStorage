<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage\Serialization\Driver;


interface IDriver 
{
    /**
     * @param mixed $data
     * @return string
     */
    public function serialize($data);

    /**
     * @param string $string
     * @return mixed
     */
    public function unserialize($string);
}