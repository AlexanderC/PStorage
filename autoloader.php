<?php
/**
 * @author AlexanderC
 */

spl_autoload_register(function ($class) {
    $path = __DIR__ . '/lib/';

    $parts = explode("\\", $class);

    if(count($parts) <= 0 || $parts[0] !== "PStorage") {
        return false;
    }

    array_shift($parts);

    $file = realpath($path . implode("/", $parts) . ".php");

    return is_file($file) ? require $file : false;
});