<?php
/**
 * @author AlexanderC
 *
 * This results are returned with native PHP serialization, MsgPack would give better results...
 * Note: that also means that in case of same field values each time would be considerable improvements
 *
 * IMPORTANT: The time of CRUD operations would increase considerably if we have an comparable value.
 *              For example for inserting 1000 entries with same data(except comparable field) when having
 *              just one comparable field will cost 19.5728 seconds. But selection of an range of values costs
 *              the same as simple selection(~ 0.0172 seconds from the middle of the tree, up to 100 rows).
 *              The only limitation is the size of binary tree file that will grow up considerable and
 *              does require a lot of RAM in cases you have more entries with different comparable fields values.
 *
 * Results, when all fields different each time:
 *  1000 entries are created with all infrastructure in 3.9936 seconds
 *  1000 entries are counted without filters in 0.0007 seconds
 *  1000 entries are returned without filters in 0.0453 seconds
 *  find one entry by multiple reference field from 1000 entries in 0.0013 seconds
 *  find one entry by single reference field from 1000 entries in 0.0010 seconds
 *  find entries range with pk offset 500 and limit 100 (#501-#601) from 1000 entries in 0.0069 seconds
 *
 * Results, when all fields same each time:
 *  1000 entries are created with all infrastructure in 4.0991 seconds
 *  1000 entries are counted without filters in 0.0008 seconds
 *  1000 entries are returned without filters in 0.0460 seconds
 *  find one entry by multiple reference field from 1000 entries in 0.1185 seconds
 *  find one entry by single reference field from 1000 entries in 0.1197 seconds
 *  find entries range with pk offset 500 and limit 100 (#501-#601) from 1000 entries in 0.0069 seconds
 */

require __DIR__ . "/Post.php";
require __DIR__ . "/Timer.php";

$timer = new Timer();

/*
$post = new Post();

$timer->start();
var_dump($post->findRangeOfComparable(500, 600, 'old_id')->count());
$timer->stop();

//*/

/*

$timer->start();
for($i = 0; $i < 1000; $i++) {
    $post = new Post();
    $post->setTitle('new title');
    $post->setText('Lorem ipsum dolor sit amet...');
    $post->setTags([
        'tag1', 'tag2', 'testtag'
    ]);
    $post->setOld_id($i);

    $post->save();
}
$timer->stop();

//*/

/*
$rand = rand(0, 100000);

$timer->start();
for($i = 0; $i < 1000; $i++) {
    $post = new Post();
    $post->setTitle('new title' . $rand);
    $post->setText('Lorem ipsum dolor sit amet...' . $rand);
    $post->setTags([
        'tag1' . $rand, 'tag2' . $rand, 'testtag' . $rand
    ]);

    $post->save();
}
$timer->stop();

//*/

/*
$post = new Post();

$timer->start();
var_dump($post->countAll());
$timer->stop();

//*/

/*
$post = new Post();

$timer->start();
var_dump($post->findAll()->count());
$timer->stop();

//*/

/*
$post = new Post();

$timer->start();
var_dump($post->findOneByTags('tag1')->getTitle());
$timer->stop();

//*/

/*
$post = new Post();

$timer->start();
var_dump($post->findOneByTitle('new title')->getTags());
$timer->stop();

//*/

/*
$post = new Post();

$timer->start();
var_dump($post->findRangeByPrimaryKey(500, 100, Post::COMPARATOR_GREATER)->count());
$timer->stop();
//*/

echo $timer->format() . "\n";