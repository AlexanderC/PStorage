<?php
/**
 * @author AlexanderC
 */

require __DIR__ . '/../autoloader.php';

use PStorage\AModel;
use PStorage\Storage\DefaultClient;
use PStorage\Storage\Drivers\FileSystemDriver;
use PStorage\Storage\Client;
use PStorage\Storage\Table;

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

    /**
     * @return array
     */
    protected function behaviors()
    {
        return [
            'slugable' => [
                'property' => 'title'
            ]
        ];
    }
}

DefaultClient::getInstance(new Client(new FileSystemDriver(__DIR__ . "/db")));

//$post = new Post();
//$post->setTitle('new title');
//$post->setText('Lorem ipsum dolor sit amet...');
//$post->setTags([
//    'tag1', 'tag2', 'testtag'
//]);

$post = new Post();
$post->getTable()->setResultOrder(Table::ORDER_DESC);

var_dump($post->findAll());exit;

foreach($post->findByTitle('new title') as $post) {
    echo $post->getSlug() , "\n";
}