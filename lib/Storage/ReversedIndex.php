<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


class ReversedIndex extends ATableSubItem
{
    const MAIN_FOLDER = "ridxf";
    const REVERSED_INDEX_FOLDER_TPL = "%s_ridx";

    const REL_ONE = 0x001;
    const REL_MANY = 0x002;

    /**
     * @var string
     */
    protected $property;

    /**
     * @var int
     */
    protected $relationType = self::REL_ONE;

    /**
     * @param int $relationType
     * @throws \OutOfBoundsException
     */
    public function setRelationType($relationType)
    {
        if(!in_array($relationType, [self::REL_ONE, self::REL_MANY])) {
            throw new \OutOfBoundsException("Relation type should be both REL_ONE or REL_MANY");
        }

        $this->relationType = $relationType;
    }

    /**
     * @return int
     */
    public function getRelationType()
    {
        return $this->relationType;
    }

    /**
     * @param string $property
     */
    public function setProperty($property)
    {
        $this->property = (string) $property;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getMainFolder()
    {
        return sprintf("%s/%s", $this->table->getMainFolder(), self::MAIN_FOLDER);
    }

    /**
     * @return string
     */
    public function getPropertyFolder()
    {
        return sprintf("%s/%s", $this->getMainFolder(), sprintf(self::REVERSED_INDEX_FOLDER_TPL, $this->property));
    }
}