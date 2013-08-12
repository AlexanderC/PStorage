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

    /**
     * @param string $file
     * @return bool
     */
    public function delete($file);

    /**
     * @param string $directory
     * @return bool
     */
    public function isDirectory($directory);

    /**
     * @param string $directory
     * @param int $rights
     * @return bool
     */
    public function createDirectory($directory, $rights = 0766);

    /**
     * @param string $pattern
     * @return array
     */
    public function & glob($pattern);
}