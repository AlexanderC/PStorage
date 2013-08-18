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
            'slug' => self::ONE | self::REQUIRED | self::UNIQUE,
            'old_id' => self::ONE | self::UNIQUE
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

    /**
     * @return array
     */
    protected function comparators()
    {
        return [
            'old_id' => self::PROPERTY_NUMBER_COMPARATOR
        ];
    }
}

DefaultClient::getInstance(new Client(new FileSystemDriver(__DIR__ . "/db")));

$post = new Post();

/*
$tree = new \PStorage\Storage\Tree\BalancedBinaryTree(new \PStorage\Model\Comparators\NumberComparator());
/** @var \PStorage\Storage\Tree\BalancedBinaryTree $tree */
/*$tree = unserialize('O:40:"PStorage\Storage\Tree\BalancedBinaryTree":1:{s:7:" * root";O:26:"PStorage\Storage\Tree\Node":5:{s:9:" * parent";N;s:7:" * left";O:26:"PStorage\Storage\Tree\Node":5:{s:9:" * parent";r:2;s:7:" * left";O:26:"PStorage\Storage\Tree\Node":5:{s:9:" * parent";r:4;s:7:" * left";N;s:8:" * right";O:26:"PStorage\Storage\Tree\Node":5:{s:9:" * parent";r:6;s:7:" * left";N;s:8:" * right";N;s:7:" * data";a:0:{}s:6:" * key";i:9;}s:7:" * data";a:0:{}s:6:" * key";i:5;}s:8:" * right";O:26:"PStorage\Storage\Tree\Node":5:{s:9:" * parent";r:4;s:7:" * left";O:26:"PStorage\Storage\Tree\Node":5:{s:9:" * parent";r:17;s:7:" * left";O:26:"PStorage\Storage\Tree\Node":5:{s:9:" * parent";r:19;s:7:" * left";N;s:8:" * right";N;s:7:" * data";a:0:{}s:6:" * key";i:18;}s:8:" * right";N;s:7:" * data";a:0:{}s:6:" * key";i:22;}s:8:" * right";N;s:7:" * data";a:3:{i:0;i:3;i:1;i:20;i:2;i:15;}s:6:" * key";i:23;}s:7:" * data";a:0:{}s:6:" * key";i:10;}s:8:" * right";O:26:"PStorage\Storage\Tree\Node":5:{s:9:" * parent";r:2;s:7:" * left";O:26:"PStorage\Storage\Tree\Node":5:{s:9:" * parent";r:38;s:7:" * left";N;s:8:" * right";N;s:7:" * data";a:0:{}s:6:" * key";i:50;}s:8:" * right";O:26:"PStorage\Storage\Tree\Node":5:{s:9:" * parent";r:38;s:7:" * left";N;s:8:" * right";N;s:7:" * data";a:0:{}s:6:" * key";i:70;}s:7:" * data";a:0:{}s:6:" * key";i:60;}s:7:" * data";a:0:{}s:6:" * key";i:40;}}');
$tree->setComparator(new \PStorage\Model\Comparators\NumberComparator());
$tree->insert(35);

$indexes = [3, 20, 15];
$property = [40, 10, 23, 5, 60, 22, 18, 9, 70, 50];
$randKey = (int) rand(0, 9);
*/
/*

----------|70|
---------------|60|
-----|50|
----------|40|
---------------|35|
|23|
----------|22|
---------------|18|
-----|10|
----------|9|
---------------|5|

 */

/*
var_dump(array_keys($tree->findGreater(18)));

var_dump(array_keys($tree->findLess(18)));

exit($tree->_dump());
*/
/*
foreach($property as $key => $value) {
    $node = $tree->insert($value);

    if($key === $randKey) {
        echo "setting data for $value\n";
        $node->setData($indexes);
    }
}

exit(serialize($tree));
//*/

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
    $post->setOld_id($i);
    $post->save();
}

//var_dump($post->getId());
//*/

///*
foreach($post->findGreaterOfComparable(99, 'old_id') as $post) {
    echo "found post #", $post->old_id, "\n";
}
//*/

///*
//$row = $post->findOneByTags('tag1');
//var_dump($row);

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