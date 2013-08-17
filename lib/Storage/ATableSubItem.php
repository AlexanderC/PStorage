<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


abstract class ATableSubItem
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * @return \PStorage\Storage\Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    abstract public function getMainFolder();
}