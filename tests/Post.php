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

/**
 * Each model should extend AModel class
 * to get it working
 */
class Post extends AModel
{
    /**
     * This method result would be used for defining
     * table structure (aka scheme)
     *  [field aka property]
     *
     * self::PK - means primary key definition.
     *              This value gets increasing and used for fast entries look up's.
     *              This is pretty same as the Primary Key in Sql engine
     *
     * self::ONE - means that the field is represented by an single value
     * self::MANY - means that the field is represented by an array of values.
     *              This is important when you have to search an single value from that array.
     *
     * self::REQUIRED - means that the field should not be empty when persisting
     * self::UNIQUE - means that the field would be checked for duplicates in the table,
     *                  and an exception would be thrown if so (aka Constrain violation in Sql engines)
     *
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
     * Behaviours are classes located at PStorage\Model\Behaviors namespace
     * that extends PStorage\Model\Behaviors\ABehavior abstract class.
     * We can define 2 kind of behaviours: Pre and Post persist.
     *
     * For example check Slugable Behaviour that will generate unique slugs
     * for title field before persisting into DB
     *
     * Note: the key in returned array should be first part of behaviour class names,
     *          for example slugable will be transformed into Slugable and than added
     *          an Behaviour suffix. After manipulations we will get SlugableBehaviour class name.
     *
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
     * Comparators are used for managing advanced range queries on
     * some fields. You can define you comparator by simple extending
     * PStorage\Model\Comparators\AComparator class.
     * The key in this array means field name and the value is the
     * instance of comparator class of the first part of this, but in
     * the last case comparator itself should be located in PStorage\Model\Comparators
     * namespace (number -> NumberComparator).
     *
     * Note that comparator method is pretty same that an callback for
     * usort or uksort functions in native PHP.
     *
     * If you define this for an field you are able to perform 3 more
     * search operations on that fields.
     *
     * ADDITIONAL METHODS ALLOWED:
     *
     * Find rows with field values located between $offset and $property
     * - public function findRangeOfComparable($offset, $limit, $property, Client $client = null)
     *
     * Find rows with field values greater than given $property
     * - public function findGreaterOfComparable($value, $property, Client $client = null)
     *
     * Find rows with field values less than given $property
     * - public function findLessOfComparable($value, $property, Client $client = null)
     *
     * ($property aka field)
     *
     * @return array
     */
    protected function comparators()
    {
        return [
            'old_id' => self::PROPERTY_NUMBER_COMPARATOR /* "number" */
        ];
    }

    /**
     * This hook is called before persisting data
     *
     * @return void
     */
    protected function prePersist()
    {
        // THIS IS REQUIRED!
        parent::prePersist();

        // do whatever you want...
    }

    /**
     * This hook is called after persisting data
     *
     * @return void
     */
    protected function postPersist()
    {
        // THIS IS REQUIRED!
        parent::postPersist();

        // do whatever you want...
    }
}

// -------------------------------------------------------- //

/**
 * Creating a new connection is pretty simple.
 * You have to set default client that would be used to persist
 * data. In any case you have ability to change it for each
 * method called.
 *
 * FileSystemDriver- means that DB files would be stored locally in
 * `__DIR__ . "/db"` folder passed as first argument
 *
 * Default serializer used is native PHP serialize/unserialize
 * Another drivers can be found in PStorage\Storage\Serialization\Driver
 * namespace and defined by passing instance of that as the second argument
 * to the Client class
 */
DefaultClient::getInstance(new Client(new FileSystemDriver(__DIR__ . "/db")/* , Serializer... */));

// -------------------------------------------------------- //

// Creating a new post is pretty simple
$post = new Post();
$post->setTitle('New title');
$post->setText('Lorem ipsum dolor sit amet...');
$post->setTags([
    'tag1', 'tag2', 'testtag'
]);
$post->setOld_id(10);

try {
    $postId = $post->save();
} catch (\Exception $e) {
    exit("Unable to add given post");
}

// -------------------------------------------------------- //

// So we can retrieve this post later, by id
$post = (new Post())->findOneById($postId);

if(false === $post) {
    exit("Hey, missing post with id #{$postId}");
}

// we ca also change something here and save after
$post->setTitle('Awesome title');

try {
    $post->save();
} catch (\Exception $e) {
    exit("Unable to save given post");
}

// also we can easily delete it...
try {
    $post->delete();
} catch (\Exception $e) {
    exit("Unable to delete given post");
}

// -------------------------------------------------------- //

// Here is a list of operations that can be performed on top of the model/* \PStorage\Collection */

// Find all rows in the table
/* \PStorage\Collection */ $post->findAll();

// Count all rows in the table
/* integer */ $post->countAll();

// Find all rows using filters (match all applied)
/* \PStorage\Collection */ $post->findBy(['title' => 'New title']);

// Count all rows using filters (match all applied)
/* integer */ $post->countBy(['title' => 'New title']);

// Find first row using filters (match all applied)
/* AModel */ $post->findOneBy(['title' => 'New title']);

// NOTE: last 3 methods can be applied more verbose thanks to magic methods (__call)
// So you can just do something like:
/* AModel */ $post->findOneByTitleAndTags('New title', 'testtag');

// We can get a range of rows for the primary key (find rows with ids starting with 1 limit 10 [first ten entries])
/* \PStorage\Collection */ $post->findRangeByPrimaryKey(1, 10);

// NOTE: Do not forget about special methods for any comparable field
// for more details see "protected function comparators()" method doc block

// -------------------------------------------------------- //

// For paginating entries we ca use helper class
// following means: show 10 entries staring with id 50
$paginator = new Paginator($post, 50, 10);

foreach(/* \PStorage\Collection */ $paginator->getEntries() as $entry) {
    echo "entry #{$entry->getId()}\n";
}

// -------------------------------------------------------- //

// We can se result order by simple using following
$post->getTable()->setResultOrder(Table::ORDER_ASC /* Table::ORDER_DESC */);