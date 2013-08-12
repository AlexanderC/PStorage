<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


use PStorage\AModel;
use PStorage\Model\TableDescriptorsConstants;
use PStorage\Model\TableTrait;
use PStorage\Storage\Drivers\IDriver;
use PStorage\Storage\Serialization\Driver\IDriver as SerializationIDriver;

class Table implements TableDescriptorsConstants
{
    use TableTrait;

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
     * @var Serialization\Driver\IDriver
     */
    protected $serializer;

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
     * @var int
     */
    protected $order = self::ORDER_ASC;

    /**
     * @param AModel $model
     * @param IDriver $storage
     * @param SerializationIDriver $serializer
     */
    public function __construct(AModel $model, IDriver $storage, SerializationIDriver $serializer)
    {
        $this->model = $model;
        $this->storage = $storage;
        $this->serializer = $serializer;

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
     * @param int $order
     * @throws \BadMethodCallException
     */
    public function setResultOrder($order)
    {
        if(!in_array($order, [self::ORDER_ASC, self::ORDER_DESC])) {
            throw new \BadMethodCallException("Result order can be both ORDER_ASC or ORDER_DESC");
        }

        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getResultOrder()
    {
        return $this->order;
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
     * @param IDriver $serializer
     */
    public function setSerializer(IDriver $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param SerializationIDriver $storage
     */
    public function setStorage(SerializationIDriver $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return \PStorage\Storage\Serialization\Driver\IDriver
     */
    public function getSerializer()
    {
        return $this->serializer;
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
     * @throws \RuntimeException
     * @return void
     */
    public function assureStructure()
    {
        $result = true;

        // check only main table folder due of overhead
        if(!$this->storage->isDirectory($this->getMainFolder())) {
            $result &= $this->storage->createDirectory($this->getMainFolder());
            $result &= $this->storage->createDirectory($this->primaryKey->getMainFolder());

            $i = 0;
            foreach($this->reversedIndexes as $reversedIndex) {
                if(0 === $i) {
                    $result &= $this->storage->createDirectory($reversedIndex->getMainFolder());
                    $i++;
                }

                $result &= $this->storage->createDirectory($reversedIndex->getPropertyFolder());
            }

            $result &= $this->storage->write($this->primaryKey->getIncrementalFile(), 1);
        }

        if(!$result) {
            throw new \RuntimeException("Unable to create {$this->getName()} table structure");
        }
    }
}