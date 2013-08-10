<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


use PStorage\AModel;
use PStorage\Storage\Drivers\IDriver;

class Table
{
    const MAIN_FOLDER_TPL = "%s_stf";

    /**
     * @var Drivers\IDriver
     */
    protected $storage;

    /**
     * @var \PStorage\AModel
     */
    protected $model;

    /**
     * @var PrimaryKey
     */
    protected $primaryKey;

    /**
     * @var array
     */
    protected $reversedIndexes = [];

    /**
     * @var Row
     */
    protected $row;

    /**
     * @param AModel $model
     * @param IDriver $storage
     */
    public function __construct(AModel $model, IDriver $storage)
    {
        $this->model = $model;
        $this->storage = $storage;

        $this->primaryKey = new PrimaryKey($this);
        $this->row = new Row($this);

        foreach($this->model->getDefinition()->getAllPropertiesExceptPrimaryKey() as $property) {
            $this->reversedIndexes[$property] = new ReversedIndex($this);
            $this->reversedIndexes[$property]->setProperty($property);
            $this->reversedIndexes[$property]->setRelationType(ReversedIndex::REL_ONE); // assure this
        }

        foreach($this->model->getDefinition()->getManyRelationProperties() as $manyRelProperty) {
            $this->reversedIndexes[$manyRelProperty]->setRelationType(ReversedIndex::REL_MANY);
        }


        $this->assureStructure();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return Inflector::modelToFolderName($this->model);
    }

    /**
     * @return string
     */
    public function getMainFolder()
    {
        return sprintf(self::MAIN_FOLDER_TPL, $this->getName());
    }

    /**
     * @return \PStorage\AModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return \PStorage\Storage\Drivers\IDriver
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return \PStorage\Storage\Row
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @return array
     */
    public function getReversedIndexes()
    {
        return $this->reversedIndexes;
    }

    /**
     * @return \PStorage\Storage\PrimaryKey
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return void
     */
    public function assureStructure()
    {
        // check only main table folder due of overhead
        if(!$this->storage->isDirectory($this->getMainFolder())) {
            $this->storage->createDirectory($this->getMainFolder());
            $this->storage->createDirectory($this->primaryKey->getMainFolder());

            foreach($this->reversedIndexes as $reversedIndex) {
                $this->storage->createDirectory($reversedIndex->getMainFolder());
                $this->storage->createDirectory($reversedIndex->getPropertyFolder());
            }
        }
    }
}