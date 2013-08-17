<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage\Tree;


use PStorage\Model\Comparators\AComparator;

class BalancedBinaryTree
{
    /**
     * @var Node|null
     */
    protected $root = null;

    /**
     * @var AComparator
     */
    protected $comparator;

    /**
     * @param AComparator $comparator
     */
    public function __construct(AComparator $comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * @param mixed $value
     * @return Node|false
     */
    public function delete($value)
    {
        if(false === ($node = $this->find($value))) {
            return false;
        }

        // because this is the root...
        if(null === $node->getParent()) {
            $this->root = null;
            return $node;
        }

        $parentNode = $node->getParent();

        // does not have children at all
        if(null === $node->getRight() && null === $node->getLeft()) {
            if($this->comparator->compare($value, $parentNode->getKey()) === AComparator::LESS) {
                $parentNode->setLeft();
            } else {
                $parentNode->setRight();
            }
        } elseif(null === ($rightNode = $node->getRight()) || null === ($leftNode = $node->getLeft())) {
            $replacement = $rightNode instanceof Node ? $rightNode : $leftNode;

            if($this->comparator->compare($value, $parentNode->getKey()) === AComparator::LESS) {
                $parentNode->setLeft($replacement);
            } else {
                $parentNode->setRight($replacement);
            }
        } else { // both values available
            $leftNode = $node->getLeft();

            // right right-most child in the left-most successor
            $current = $leftNode;

            do {
                $found = $current;
            } while(null !== ($current = $current->getRight()));

            $node->setData($found->getData());
            $node->setKey($found->getKey());

            if(null !== ($leftNode = $found->getLeft())) {
                $found->setData($leftNode->getData());
                $found->setKey($leftNode->getKey());

                if(null === ($rightChildNode = $leftNode->getRight())) {
                    $found->setRight();
                } else {
                    $found->setRight($rightChildNode);
                    $rightChildNode->setParent($found);
                }

                if(null === ($leftChildNode = $leftNode->getLeft())) {
                    $found->setLeft();
                } else {
                    $found->setLeft($leftChildNode);
                    $leftChildNode->setParent($found);
                }
            }
        }

        $this->balance();

        return $node;
    }

    /**
     * @param mixed $value
     * @return Node
     * @throws \OutOfBoundsException
     */
    public function insert($value)
    {
        if($this->find($value) instanceof Node) {
            throw new \OutOfBoundsException("{$value} already exists in the tree");
        }

        if(null === $this->root)
        {
            $this->root = new Node($value);
            return $this->root;
        }

        $current = $this->root;

        do {
            if($this->comparator->compare($value, $current->getKey()) === AComparator::LESS) {
                $next = $current->getLeft();

                if(null === $next) {
                    $next = new Node($value);
                    $next->setParent($current);
                    $current->setLeft($next);

                    $this->balance();
                    return $next;
                }
            } else {
                $next = $current->getRight();

                if(null === $next) {
                    $next = new Node($value);
                    $next->setParent($current);
                    $current->setRight($next);

                    $this->balance();
                    return $next;
                }
            }

            $current = $next;
        } while(true);
    }

    /**
     * @param mixed $value
     * @return bool|Node
     */
    public function find($value)
    {
        if(null === $this->root) {
            return false;
        }

        $current = $this->root;

        do {
            $comparisonResult = $this->comparator->compare($value, $current->getKey());

            if(AComparator::LESS === $comparisonResult) {
                $next = $current->getLeft();
            } elseif(AComparator::GREATER === $comparisonResult) {
                $next = $current->getRight();
            } else {
                return $current;
            }

            $current = $next;
        } while($current instanceof Node);

        return false;
    }

    /**
     * @return void
     */
    protected function balance()
    {

    }

    /**
     * @return \PStorage\Model\Comparators\AComparator
     */
    public function getComparator()
    {
        return $this->comparator;
    }

    /**
     * @param \PStorage\Model\Comparators\AComparator $comparator
     */
    public function setComparator(AComparator $comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [
            'root'
        ];
    }
}