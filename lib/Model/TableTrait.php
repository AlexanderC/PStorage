<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Model;

use PStorage\Collection;
use PStorage\AModel;
use PStorage\Storage\Exceptions\AtomarityViolationException;
use PStorage\Storage\Exceptions\MissingPrimaryKeyException;
use PStorage\Storage\Exceptions\MissingSearchValueException;
use PStorage\Storage\PrimaryKey;
use PStorage\Storage\ReversedIndex;
use PStorage\Storage\Table;
use PStorage\Storage\Tree\BalancedBinaryTree;
use PStorage\Storage\Tree\Node;

trait TableTrait 
{
    /**
     * @var \PStorage\AModel
     */
    protected $model;

    /**
     * @var Serialization\Driver\IDriver
     */
    protected $serializer;

    /**
     * @return int
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     */
    public function add()
    {
        $this->model->validate();

        /** @var Table $table */
        $table = $this;

        $primaryKey = $table->getPrimaryKey();
        $serializer = $table->getSerializer();
        $storage = $table->getStorage();
        $definition = $this->model->getDefinition();

        $index = $primaryKey->getIncrementalValue();

        $indexFile = $primaryKey->getIndexFile($index);
        $rowContentFolder = $primaryKey->getRowContentFolder($index);

        $indexFileData = [];

        if($storage->exists($indexFile)) {
            $indexFileData = $serializer->unserialize($storage->read($indexFile));
        } else {
            if(!$storage->isDirectory($rowContentFolder)) {
                if(!$storage->createDirectory($rowContentFolder)) {
                    throw new AtomarityViolationException("Unable to create row content folder");
                }
            }
        }

        $rowContent = [];
        $rowProperties = $definition->getAllPropertiesExceptPrimaryKey();

        foreach($rowProperties as $property) {
            $rowContent[$property] = $this->model->$property;
        }
        $rowContent[$definition->getPrimaryKeyProperty()] = $index;

        do {
            $rowContentFile = $table->getRow()->getRowContentFile(
                $rowContentFolder,
                basename(tempnam($rowContentFolder, md5(microtime(true)) . "_"))
            );
        } while($storage->exists($rowContentFile));

        if(!$storage->write($rowContentFile, $serializer->serialize($rowContent))) {
            throw new AtomarityViolationException("Unable to persist model data");
        }

        $indexFileData[$index] = $rowContentFile;

        if(!$storage->write($indexFile, $serializer->serialize($indexFileData))) {
            throw new AtomarityViolationException("Unable to persist model data index relation");
        }

        $manyRelationProperties = $definition->getManyRelationProperties();

        foreach($rowProperties as $property) {
            /** @var ReversedIndex $reversedIndex */
            $reversedIndex = $table->getReversedIndexes()[$property];

            if(AModel::DEFAULT_VALUE !== $table->getModel()->$property
                && $table->getModel()->hasComparator($property)) {

                $searchTree = $table->getSearchTrees()[$property];

                $treeFile = $searchTree->getTreeFile();

                if($storage->exists($treeFile)) {
                    /** @var BalancedBinaryTree $tree */
                    $tree = @unserialize($storage->read($treeFile));

                    if(false === $tree) {
                        throw new AtomarityViolationException("Unable to read property binary search tree file");
                    }

                    $tree->setComparator($table->getModel()->getComparators()[$property]);
                } else {
                    /** @var BalancedBinaryTree $tree */
                    $tree = new BalancedBinaryTree($table->getModel()->getComparators()[$property]);
                }

                $value = $table->getModel()->$property;
                /** @var Node $node */
                if($node = $tree->find($value) instanceof Node) {
                    $data = $node->getData();
                    $data[] = $index;
                    $node->setData($data);
                } else {
                    $node = $tree->insert($value);
                    $node->setData([$index]);
                }

                if(!$storage->write($treeFile, serialize($tree))) {
                    throw new AtomarityViolationException(
                        "Unable to persist modified property binary search tree file"
                    );
                }
            }

            if(!in_array($property, $manyRelationProperties)) {
                $reversedIndexSubfolder = $reversedIndex->getReversedIndexSubfolder($rowContent[$property]);

                if(!$storage->isDirectory($reversedIndexSubfolder)) {
                    if(!$storage->createDirectory($reversedIndexSubfolder)) {
                        throw new AtomarityViolationException("Unable to create reversed index sub folder");
                    }
                }

                $reversedIndexFile = $reversedIndex->getReversedIndexFile($rowContent[$property]);

                $reversedIndexContent = [];

                if($storage->exists($reversedIndexFile)) {
                    $reversedIndexContent = $serializer->unserialize($storage->read($reversedIndexFile));
                }

                $reversedIndexContent[] = $index;

                if(!$storage->write($reversedIndexFile, $serializer->serialize($reversedIndexContent))) {
                    throw new AtomarityViolationException("Unable to persist reversed index data");
                }
            } else {
                foreach($rowContent[$property] as $subPropertyValue) {
                    $reversedIndexSubfolder = $reversedIndex->getReversedIndexSubfolder($subPropertyValue);

                    if(!$storage->isDirectory($reversedIndexSubfolder)) {
                        if(!$storage->createDirectory($reversedIndexSubfolder)) {
                            throw new AtomarityViolationException("Unable to create reversed index sub folder");
                        }
                    }

                    $reversedIndexFile = $reversedIndex->getReversedIndexFile($subPropertyValue);

                    $reversedIndexContent = [];

                    if($storage->exists($reversedIndexFile)) {
                        $reversedIndexContent = $serializer->unserialize($storage->read($reversedIndexFile));
                    }

                    $reversedIndexContent[] = $index;

                    if(!$storage->write($reversedIndexFile, $serializer->serialize($reversedIndexContent))) {
                        throw new AtomarityViolationException("Unable to persist reversed index data");
                    }
                }
            }
        }

        return $index;
    }

    /**
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     * @throws \PStorage\Storage\Exceptions\MissingPrimaryKeyException
     * @return void
     */
    public function save()
    {
        $this->model->validate(true);

        if(!$this->model->hasPrimaryKey()) {
            throw new MissingPrimaryKeyException("Missing primary key when trying to persist");
        }

        $table = $this;

        $primaryKey = $table->getPrimaryKey();
        $serializer = $table->getSerializer();
        $storage = $table->getStorage();
        $definition = $this->model->getDefinition();
        $primaryKeyProperty = $definition->getPrimaryKeyProperty();

        $index = $this->model->$primaryKeyProperty;

        $indexFileData = [];
        $reversedIndexFiles = $this->getReversedIndexFiles($indexFileData);
        $rowContentFile = $indexFileData[$index];

        $rowContent = [];
        $rowProperties = $definition->getAllProperties();

        foreach($rowProperties as $property) {
            $rowContent[$property] = $this->model->$property;
        }

        if(!$storage->write($rowContentFile, $serializer->serialize($rowContent))) {
            throw new AtomarityViolationException("Unable to persist changed model data");
        }

        // delete all old reversed indexes
        foreach($reversedIndexFiles as $reversedIndexFile) {
            $reversedIndexContent = $serializer->unserialize($storage->read($reversedIndexFile));

            $keysToUnset = array_keys($reversedIndexContent, $index, true);

            foreach($keysToUnset as $key) {
                unset($reversedIndexContent[$key]);
            }

            if(!$storage->write($reversedIndexFile, $serializer->serialize($reversedIndexContent))) {
                throw new AtomarityViolationException("Unable to persist modified reversed index file");
            }
        }

        $manyRelationProperties = $definition->getManyRelationProperties();

        foreach($definition->getAllPropertiesExceptPrimaryKey() as $property) {
            /** @var ReversedIndex $reversedIndex */
            $reversedIndex = $table->getReversedIndexes()[$property];

            if(!in_array($property, $manyRelationProperties)) {
                $reversedIndexSubfolder = $reversedIndex->getReversedIndexSubfolder($rowContent[$property]);

                if(!$storage->isDirectory($reversedIndexSubfolder)) {
                    if(!$storage->createDirectory($reversedIndexSubfolder)) {
                        throw new AtomarityViolationException("Unable to create reversed index sub folder");
                    }
                }

                $reversedIndexFile = $reversedIndex->getReversedIndexFile($rowContent[$property]);

                $reversedIndexContent = [];

                if($storage->exists($reversedIndexFile)) {
                    $reversedIndexContent = $serializer->unserialize($storage->read($reversedIndexFile));
                }

                $reversedIndexContent[] = $index;

                if(!$storage->write($reversedIndexFile, $serializer->serialize($reversedIndexContent))) {
                    throw new AtomarityViolationException("Unable to persist reversed index data");
                }
            } else {
                foreach($rowContent[$property] as $subPropertyValue) {
                    $reversedIndexSubfolder = $reversedIndex->getReversedIndexSubfolder($subPropertyValue);

                    if(!$storage->isDirectory($reversedIndexSubfolder)) {
                        if(!$storage->createDirectory($reversedIndexSubfolder)) {
                            throw new AtomarityViolationException("Unable to create reversed index sub folder");
                        }
                    }

                    $reversedIndexFile = $reversedIndex->getReversedIndexFile($subPropertyValue);

                    $reversedIndexContent = [];

                    if($storage->exists($reversedIndexFile)) {
                        $reversedIndexContent = $serializer->unserialize($storage->read($reversedIndexFile));
                    }

                    $reversedIndexContent[] = $index;

                    if(!$storage->write($reversedIndexFile, $serializer->serialize($reversedIndexContent))) {
                        throw new AtomarityViolationException("Unable to persist reversed index data");
                    }
                }
            }
        }
    }

    /**
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     * @throws \PStorage\Storage\Exceptions\MissingPrimaryKeyException
     * @return void
     */
    public function delete()
    {
        if(!$this->model->hasPrimaryKey()) {
            throw new MissingPrimaryKeyException("Missing primary key when trying to delete");
        }

        $table = $this;

        $primaryKey = $table->getPrimaryKey();
        $serializer = $table->getSerializer();
        $storage = $table->getStorage();
        $definition = $this->model->getDefinition();
        $primaryKeyProperty = $definition->getPrimaryKeyProperty();

        $index = $this->model->$primaryKeyProperty;
        $indexFile = $primaryKey->getIndexFile($index);


        $indexFileData = [];
        $reversedIndexFiles = $this->getReversedIndexFiles($indexFileData);
        $rowContentFile = $indexFileData[$index];

        if(!$storage->delete($rowContentFile)) {
            throw new AtomarityViolationException("Unable to delete row content file");
        }

        unset($indexFileData[$index]);
        if(!$storage->write($indexFile, $serializer->serialize($indexFileData))) {
            throw new AtomarityViolationException("Unable to persist modified index file");
        }

        foreach($reversedIndexFiles as $reversedIndexFile) {
            $reversedIndexContent = $serializer->unserialize($storage->read($reversedIndexFile));

            $keysToUnset = array_keys($reversedIndexContent, $index, true);

            foreach($keysToUnset as $key) {
                unset($reversedIndexContent[$key]);
            }

            if(!$storage->write($reversedIndexFile, $serializer->serialize($reversedIndexContent))) {
                throw new AtomarityViolationException("Unable to persist modified reversed index file");
            }
        }
    }

    /**
     * @param array $indexFileData
     * @return array
     * @throws \RuntimeException
     * @throws \PStorage\Storage\Exceptions\MissingSearchValueException
     */
    protected function & getReversedIndexFiles(array & $indexFileData)
    {
        /** @var Table $table */
        $table = $this;

        $primaryKey = $table->getPrimaryKey();
        $serializer = $table->getSerializer();
        $storage = $table->getStorage();
        $definition = $this->model->getDefinition();
        $reversedIndexes = $table->getReversedIndexes();

        $primaryKeyProperty = $definition->getPrimaryKeyProperty();
        $allProperties = $definition->getAllPropertiesExceptPrimaryKey();
        $manyRelationProperties = $definition->getManyRelationProperties();

        $index = $this->model->$primaryKeyProperty;
        $indexFile = $primaryKey->getIndexFile($index);

        if(!$storage->exists($indexFile)) {
            throw new MissingSearchValueException("Index file not found");
        }

        $indexFileData = $serializer->unserialize($storage->read($indexFile));

        if(!isset($indexFileData[$index])) {
            throw new MissingSearchValueException("Index file does not contain searched index");
        }

        $rowContentFile = $indexFileData[$index];

        // manager case when is missing row content file
        if(!$storage->exists($rowContentFile)) {
            unset($indexFileData[$index]);

            if(!$storage->write($indexFile, $indexFileData)) {
                throw new MissingSearchValueException("Unable to persist repaired index file");
            }

            throw new \RuntimeException("Missing row data file");
        }

        $rowData = $serializer->unserialize($storage->read($rowContentFile));

        $reversedIndexFiles = [];

        foreach($allProperties as $property) {
            if(!in_array($property, $manyRelationProperties)) {
                $reversedIndexFiles[] = $reversedIndexes[$property]->getReversedIndexFile($rowData[$property]);
            } else {
                foreach($rowData[$property] as $propertyContent) {
                    $reversedIndexFiles[] = $reversedIndexes[$property]->getReversedIndexFile($propertyContent);
                }
            }
        }

        return $reversedIndexFiles;
    }

    /**
     * @return Collection
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     */
    public function findAll()
    {
        /** @var Table $table */
        $table = $this;

        $primaryKey = $table->getPrimaryKey();
        $serializer = $table->getSerializer();
        $storage = $table->getStorage();

        $indexFiles = $storage->glob($primaryKey->getIndexFilesGlobPattern());

        $result = [];

        foreach($indexFiles as $indexFile) {
            $result[$indexFile] = $serializer->unserialize($storage->read($indexFile));
        }

        if(count($result) <= 0) {
            return new Collection();
        }

        $resultData = [];

        foreach($result as $indexFile => $indexFileData) {
            foreach($indexFileData as $index => $rowContentFile) {
                // manager case when is missing row content file
                if(!$storage->exists($rowContentFile)) {
                    unset($indexFileData[$index]);

                    if(!$storage->write($indexFile, $serializer->serialize($indexFileData))) {
                        throw new AtomarityViolationException("Unable to persist repaired index file");
                    }

                    continue;
                }

                $resultData[] = $serializer->unserialize($storage->read($rowContentFile));
            }
        }

        return new Collection($resultData);
    }

    /**
     * @return int
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     */
    public function countAll()
    {
        /** @var Table $table */
        $table = $this;

        $primaryKey = $table->getPrimaryKey();
        $serializer = $table->getSerializer();
        $storage = $table->getStorage();

        $indexFiles = $storage->glob($primaryKey->getIndexFilesGlobPattern());

        $result = 0;

        foreach($indexFiles as $indexFile) {
            $indexFileData = $serializer->unserialize($storage->read($indexFile));
            $initialIndexesCount = count($indexFileData);

            foreach($indexFileData as $index => $rowContentFile) {
                if(!$storage->exists($rowContentFile)) {
                    unset($indexFileData[$index]);
                }
            }

            // manager case when is missing one or more row content files
            if(($indexesCount = count($indexFileData)) !== $initialIndexesCount) {
                if(!$storage->write($indexFile, $serializer->serialize($indexFileData))) {
                    throw new AtomarityViolationException("Unable to persist repaired index file");
                }
            }

            $result += count($indexFileData);
        }

        return $result;
    }

    /**
     * @param array $filters
     * @return Collection
     */
    public function findBy(array $filters)
    {
        /** @var Table $table */
        $table = $this;

        $serializer = $table->getSerializer();
        $storage = $table->getStorage();
        $resultSet = $this->findAllIndexesBy($filters);

        if(count($resultSet) > 0) {
            $data = [];

            foreach($resultSet as $rowContentFile => $index) {
                $data[] = $serializer->unserialize($storage->read($rowContentFile));
            }

            return $this->model->createCollectionFromArray($data);
        }

        return new Collection();
    }

    /**
     * @param array $filters
     * @return int
     */
    public function countBy(array $filters)
    {
        return count($this->findAllIndexesBy($filters));
    }

    /**
     * @param array $filters
     * @return AModel|bool
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     * @throws \BadMethodCallException
     */
    public function findOneBy(array $filters)
    {
        /** @var Table $table */
        $table = $this;

        $serializer = $table->getSerializer();
        $storage = $table->getStorage();
        $resultSet = $this->findAllIndexesBy($filters);

        if(count($resultSet) > 0) {
            $resultRowFile = key($resultSet);

            $modelClass = get_class($this->model);

            return new $modelClass($serializer->unserialize($storage->read($resultRowFile)));
        }

        return false;
    }

    /**
     * @param array $filters
     * @param bool $order
     * @return array
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     * @throws \BadMethodCallException
     */
    protected function & findAllIndexesBy(array & $filters, $order = true)
    {
        /** @var Table $table */
        $table = $this;

        $primaryKey = $table->getPrimaryKey();
        $serializer = $table->getSerializer();
        $storage = $table->getStorage();
        $definition = $this->model->getDefinition();
        $reversedIndexes = $table->getReversedIndexes();

        $allProperties = $definition->getAllPropertiesExceptPrimaryKey();
        $primaryKeyProperty = $definition->getPrimaryKeyProperty();
        $manyRelationProperties = $definition->getManyRelationProperties();

        foreach($filters as $property => $filterValue) {
            if(!in_array($property, $allProperties) && $property !== $primaryKeyProperty) {
                throw new \BadMethodCallException("Unknown property {$property} in properties list");
            }
        }

        $matches = [];

        foreach($filters as $property => $filterValue) {
            $foundIndexes = [];

            if($property === $primaryKeyProperty) { // easy dude...
                $indexFile = $primaryKey->getIndexFile($filterValue);

                if(!$storage->exists($indexFile)) {
                    $matches[] = $foundIndexes;
                    break;
                }

                $indexFileData = $serializer->unserialize($storage->read($indexFile));

                if(!isset($indexFileData[$filterValue])) {
                    $matches[] = $foundIndexes;
                    break;
                }

                $rowContentFile = $indexFileData[$filterValue];

                // manager case when is missing row content file
                if(!$storage->exists($rowContentFile)) {
                    unset($indexFileData[$filterValue]);

                    if(!$storage->write($indexFile, $serializer->serialize($indexFileData))) {
                        throw new AtomarityViolationException("Unable to persist repaired index file");
                    }

                    $matches[] = $foundIndexes;
                    break;
                }

                $foundIndexes[$rowContentFile] = $filterValue;
            } else {
                /** @var ReversedIndex $reversedIndex */
                $reversedIndex = $reversedIndexes[$property];

                $reversedIndexFile = $reversedIndex->getReversedIndexFile($filterValue);

                if(!$storage->exists($reversedIndexFile)) {
                    $matches[] = $foundIndexes;
                    break;
                }

                $reversedIndexContent = $serializer->unserialize($storage->read($reversedIndexFile));

                foreach($reversedIndexContent as $index) {
                    $indexFile = $primaryKey->getIndexFile($index);

                    if(!$storage->exists($indexFile)) {
                        continue;
                    }

                    $indexFileData = $serializer->unserialize($storage->read($indexFile));

                    if(!isset($indexFileData[$index])) {
                        continue;
                    }

                    $rowContentFile = $indexFileData[$index];

                    // manager case when is missing row content file
                    if(!$storage->exists($rowContentFile)) {
                        unset($indexFileData[$index]);

                        if(!$storage->write($indexFile, $serializer->serialize($indexFileData))) {
                            throw new AtomarityViolationException("Unable to persist repaired index file");
                        }

                        continue;
                    }

                    $rowData = $serializer->unserialize($storage->read($rowContentFile));

                    // case boolean match
                    if(!in_array($property, $manyRelationProperties)) {
                        if($rowData[$property] === $filterValue) {
                            $foundIndexes[$rowContentFile] = $index;
                        }
                    } else {
                        if(in_array($filterValue, $rowData[$property])) {
                            $foundIndexes[$rowContentFile] = $index;
                        }
                    }
                }
            }

            $matches[] = $foundIndexes;
        }

        $resultSet = [];
        $matchesCount = count($matches);

        // case at least 2 values
        if($matchesCount >= 2) {
            $resultSet = call_user_func_array('array_intersect', $matches);
        } else if($matchesCount == 1) {
            $resultSet = array_shift($matches);
        }

        if($order) {
            if($table->getResultOrder() === Table::ORDER_DESC) {
                arsort($resultSet, SORT_NUMERIC);
            } else {
                asort($resultSet, SORT_NUMERIC);
            }
        }

        return $resultSet;
    }

    /**
     * @param mixed $value
     * @param scalar $property
     * @return Collection
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     * @throws \BadMethodCallException
     */
    public function findLessOfComparable($value, $property)
    {
        /** @var Table $table */
        $table = $this;

        $primaryKey = $table->getPrimaryKey();
        $serializer = $table->getSerializer();
        $storage = $table->getStorage();
        $definition = $this->model->getDefinition();
        $searchTree = $table->getSearchTrees()[$property];

        if(!$table->getModel()->hasComparator($property)) {
            throw new \BadMethodCallException("Comparator is not defined for this property");
        }

        $treeFile = $searchTree->getTreeFile();

        if(!$storage->exists($treeFile)) {
            return new Collection();
        }

        /** @var BalancedBinaryTree $tree */
        $tree = $serializer->unserialize($storage->read($treeFile));
        $tree->setComparator($table->getModel()->getComparators()[$property]);

        $nodes = $tree->findLess($value);

        if(!empty($nodes)) {
            $foundRows = [];

            /** @var Node $node */
            foreach($nodes as $node) {
                foreach($node->getData() as $index) {
                    $indexFile = $primaryKey->getIndexFile($index);

                    if(!$storage->exists($indexFile)) {
                        continue;
                    }

                    $indexFileData = $serializer->unserialize($storage->read($indexFile));

                    if(!isset($indexFileData[$index])) {
                        continue;
                    }

                    $rowContentFile = $indexFileData[$index];

                    // manager case when is missing row content file
                    if(!$storage->exists($rowContentFile)) {
                        unset($indexFileData[$index]);

                        if(!$storage->write($indexFile, $serializer->serialize($indexFileData))) {
                            throw new AtomarityViolationException("Unable to persist repaired index file");
                        }

                        continue;
                    }

                    $foundRows[] = $serializer->unserialize($storage->read($rowContentFile));
                }
            }

            return $table->getModel()->createCollectionFromArray($foundRows);
        }

        return new Collection();
    }

    /**
     * @param mixed $value
     * @param scalar $property
     * @return Collection
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     * @throws \BadMethodCallException
     */
    public function findGreaterOfComparable($value, $property)
    {
        /** @var Table $table */
        $table = $this;

        $primaryKey = $table->getPrimaryKey();
        $serializer = $table->getSerializer();
        $storage = $table->getStorage();
        $definition = $this->model->getDefinition();
        $searchTree = $table->getSearchTrees()[$property];

        if(!$table->getModel()->hasComparator($property)) {
            throw new \BadMethodCallException("Comparator is not defined for this property");
        }

        $treeFile = $searchTree->getTreeFile();

        if(!$storage->exists($treeFile)) {
            return new Collection();
        }

        /** @var BalancedBinaryTree $tree */
        $tree = $serializer->unserialize($storage->read($treeFile));
        $tree->setComparator($table->getModel()->getComparators()[$property]);

        $nodes = $tree->findGreater($value);

        if(!empty($nodes)) {
            $foundRows = [];

            /** @var Node $node */
            foreach($nodes as $node) {
                foreach($node->getData() as $index) {
                    $indexFile = $primaryKey->getIndexFile($index);

                    if(!$storage->exists($indexFile)) {
                        continue;
                    }

                    $indexFileData = $serializer->unserialize($storage->read($indexFile));

                    if(!isset($indexFileData[$index])) {
                        continue;
                    }

                    $rowContentFile = $indexFileData[$index];

                    // manager case when is missing row content file
                    if(!$storage->exists($rowContentFile)) {
                        unset($indexFileData[$index]);

                        if(!$storage->write($indexFile, $serializer->serialize($indexFileData))) {
                            throw new AtomarityViolationException("Unable to persist repaired index file");
                        }

                        continue;
                    }

                    $foundRows[] = $serializer->unserialize($storage->read($rowContentFile));
                }
            }

            return $table->getModel()->createCollectionFromArray($foundRows);
        }

        return new Collection();
    }

    /**
     * @param mixed $offset
     * @param mixed $limit
     * @param scalar $property
     * @return Collection
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     * @throws \BadMethodCallException
     */
    public function findRangeOfComparable($offset, $limit, $property)
    {
        /** @var Table $table */
        $table = $this;

        $primaryKey = $table->getPrimaryKey();
        $serializer = $table->getSerializer();
        $storage = $table->getStorage();
        $definition = $this->model->getDefinition();
        $searchTree = $table->getSearchTrees()[$property];

        if(!$table->getModel()->hasComparator($property)) {
            throw new \BadMethodCallException("Comparator is not defined for this property");
        }

        $treeFile = $searchTree->getTreeFile();

        if(!$storage->exists($treeFile)) {
            return new Collection();
        }

        /** @var BalancedBinaryTree $tree */
        $tree = $serializer->unserialize($storage->read($treeFile));
        $tree->setComparator($table->getModel()->getComparators()[$property]);

        $nodes = $tree->findRange($offset, $limit);

        if(!empty($nodes)) {
            $foundRows = [];

            /** @var Node $node */
            foreach($nodes as $node) {
                foreach($node->getData() as $index) {
                    $indexFile = $primaryKey->getIndexFile($index);

                    if(!$storage->exists($indexFile)) {
                        continue;
                    }

                    $indexFileData = $serializer->unserialize($storage->read($indexFile));

                    if(!isset($indexFileData[$index])) {
                        continue;
                    }

                    $rowContentFile = $indexFileData[$index];

                    // manager case when is missing row content file
                    if(!$storage->exists($rowContentFile)) {
                        unset($indexFileData[$index]);

                        if(!$storage->write($indexFile, $serializer->serialize($indexFileData))) {
                            throw new AtomarityViolationException("Unable to persist repaired index file");
                        }

                        continue;
                    }

                    $foundRows[] = $serializer->unserialize($storage->read($rowContentFile));
                }
            }

            return $table->getModel()->createCollectionFromArray($foundRows);
        }

        return new Collection();
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param int $comparator
     * @return Collection
     * @throws \PStorage\Storage\Exceptions\AtomarityViolationException
     * @throws \BadMethodCallException
     */
    public function findRangeByPrimaryKey($offset, $limit, $comparator = Table::COMPARATOR_GREATER)
    {
        if(!in_array($comparator, [Table::COMPARATOR_GREATER, Table::COMPARATOR_LESS])) {
            throw new \BadMethodCallException(
                "Range comparator key should be both COMPARATOR_GREATER or COMPARATOR_LESS"
            );
        }

        $offsetStart = abs((int) $offset);
        $limit = abs((int) $limit);

        if($offsetStart <= 0 || $limit <= 0) {
            throw new \BadMethodCallException("OffsetStart and Limit should be greater than or equal with 1");
        }

        /** @var Table $table */
        $table = $this;

        $primaryKey = $table->getPrimaryKey();
        $serializer = $table->getSerializer();
        $storage = $table->getStorage();
        $definition = $this->model->getDefinition();

        $indexFiles = array_filter(
            $storage->glob($primaryKey->getIndexFilesGlobPattern()),
            function($value) use ($offset, $limit, $comparator) {
                preg_match("/^(\d+)\.(\d+)_pk$/", basename($value), $matches);

                // o_O something weird...
                if(count($matches) !== 3) {
                    return false;
                }

                $keyStart = (int) $matches[1];
                $keyEnd = (int) $matches[2];

                if($comparator === Table::COMPARATOR_GREATER) {
                    return $keyEnd > $offset;
                } else {
                    return $keyStart < $offset;
                }
        });

        usort($indexFiles, function($a, $b) use ($comparator) {
            preg_match("/^(\d+)\.(\d+)_pk$/", basename($a), $matchesA);
            preg_match("/^(\d+)\.(\d+)_pk$/", basename($b), $matchesB);

            // o_O something weird...
            if(count($matchesA) !== 3 || count($matchesB) !== 3) {
                return 1;
            }

            $keyStartA = (int) $matchesA[1];
            $keyStartB = (int) $matchesB[1];

            if($comparator === Table::COMPARATOR_GREATER) {
                return $keyStartA > $keyStartB;
            } else {
                return $keyStartA < $keyStartB;
            }
        });

        $resultSet = [];

        foreach($indexFiles as $indexFile) {
            $indexFileData = $serializer->unserialize($storage->read($indexFile));
            $initialIndexesCount = count($indexFileData);

            if($comparator === Table::COMPARATOR_LESS) {
                $indexFileData = array_reverse($indexFileData, true);
            }

            foreach($indexFileData as $index => $rowContentFile) {
                if(!$storage->exists($rowContentFile)) {
                    unset($indexFileData[$index]);
                } else {
                    if($comparator === Table::COMPARATOR_GREATER) {
                        if($index > $offset) {
                            $resultSet[$index] = $serializer->unserialize($storage->read($rowContentFile));
                        }
                    } else {
                        if($index < $offset) {
                            $resultSet[$index] = $serializer->unserialize($storage->read($rowContentFile));
                        }
                    }
                }

                if(count($resultSet) >= $limit) {
                    // manager case when is missing one or more row content files
                    if(($indexesCount = count($indexFileData)) !== $initialIndexesCount) {
                        if(!$storage->write($indexFile, $serializer->serialize($indexFileData))) {
                            throw new AtomarityViolationException("Unable to persist repaired index file");
                        }
                    }

                    break 2;
                }
            }

            // manager case when is missing one or more row content files
            if(($indexesCount = count($indexFileData)) !== $initialIndexesCount) {
                if(!$storage->write($indexFile, $serializer->serialize($indexFileData))) {
                    throw new AtomarityViolationException("Unable to persist repaired index file");
                }
            }
        }

        if($table->getResultOrder() === Table::ORDER_DESC) {
            krsort($resultSet, SORT_NUMERIC);
        } else {
            ksort($resultSet, SORT_NUMERIC);
        }

        return $this->model->createCollectionFromArray($resultSet);
    }
}