<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


class SearchTree extends ATableSubItem
{
    const MAIN_FOLDER = "bstr";
    const SEARCH_TREE_FOLDER = "%s_bstrnds";

    /**
     * @var string
     */
    protected $property;

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
        return sprintf("%s/%s", $this->getMainFolder(), sprintf(self::SEARCH_TREE_FOLDER, $this->property));
    }
}