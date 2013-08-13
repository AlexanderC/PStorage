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
use PStorage\Helpers\Paginator;

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

/*
$post = new Post();

$paginator = new Paginator($post, 50, 10);

foreach($paginator->getEntries() as $entry) {
    echo "entry #{$entry->getId()}\n";
}
//*/

/*
$post = new Post();

var_dump($post->countAll());
//*/

/*
$post = new Post();
$post->getTable()->setResultOrder(Table::ORDER_ASC);

$entry;

//var_dump($post->findRangeByPrimaryKey(50, 5, Post::COMPARATOR_LESS)->count());
foreach($post->findRangeByPrimaryKey(50, 5, Post::COMPARATOR_GREATER) as $entry) {
    echo "entry #{$entry->getId()}\n";
}

//*/

/*
$post = new Post();
$post->setTitle('new title');
$post->setText('Lorem ipsum dolor sit amet...');
$post->setTags([
    'tag1', 'tag2', 'testtag'
]);

for($i = 0; $i < 101; $i++) {
    $post->id = Post::DEFAULT_VALUE;
    $post->save();
}

var_dump($post->getId());
//*/
/*
$post = new Post();
$post->getTable()->setResultOrder(Table::ORDER_DESC);

$single;

foreach($post->findByTags('tag1') as $single) {
    echo $single->getSlug() , "\n";
}
/*
$single->setTitle('new title with random- ' . rand(0, 10000));
var_dump($single->save());
//*/
//*/

/*
$post = new Post();
$post->getTable()->setResultOrder(Table::ORDER_ASC);

$first = $post->findOneByTags('tag1');

var_dump($first->delete());
 //*/