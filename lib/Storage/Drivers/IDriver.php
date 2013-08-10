<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage\Drivers;


interface IDriver 
{
    /**
     * @param string $file
     * @param string $string
     * @return bool
     */
    public function write($file, $string);

    /**
     * @param string $file
     * @return string
     */
    public function read($file);

    /**
     * @param string $file
     * @return bool
     */
    public function exists($file);
}