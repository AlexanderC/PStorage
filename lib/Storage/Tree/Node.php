<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage\Tree;


class Node
{
    /**
     * @var Node|null
     */
    protected $parent = null;

    /**
     * @var Node|null
     */
    protected $left = null;

    /**
     * @var Node|null
     */
    protected $right = null;

    /**
     * @var mixed
     */
    protected $key;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param mixed $key
     * @param array $data
     */
    public function __construct($key, array $data = [])
    {
        $this->key = $key;
        $this->data = $data;
    }

    /**
     * Empty the node by keeping up the key, but
     * setting up the data to NULL
     */
    public function doEmpty()
    {
        $this->data = [];
    }

    /**
     * @return null|Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Node $parent
     */
    public function setParent(Node $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return null|Node
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @param Node $left
     */
    public function setLeft(Node $left = null)
    {
        $this->left = $left;
    }

    /**
     * @return null|Node
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @param Node $right
     */
    public function setRight(Node $right = null)
    {
        $this->right = $right;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [
            'parent', 'left', 'right', 'data', 'key'
        ];
    }
}