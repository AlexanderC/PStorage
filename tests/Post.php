<?php
/**
 * @author AlexanderC
 */

require __DIR__ . '/../autoloader.php';

use PStorage\AModel;

class Post extends AModel
{
    /**
     * @return array
     */
    protected function definition()
    {
        return [
            'id' => self::PK, // self::UNIQUE by default
            'title' => self::ONE | self::REQUIRED,
            'text' => self::ONE | self::REQUIRED,
            'tags' => self::MANY | self::REQUIRED,
            'slug' => self::ONE | self::REQUIRED | self::UNIQUE
        ];
    }
}

$post = new Post();

var_dump($post->getTitle());