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
     * @var null|Node
     */
    protected $leftMost = null;

    /**
     * @var null|Node
     */
    protected $rightMost = null;

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
     * @return null|Node
     */
    public function getLeftMost()
    {
        return $this->leftMost;
    }

    /**
     * @return null|Node
     */
    public function getRightMost()
    {
        return $this->rightMost;
    }

    /**
     * @return null|\PStorage\Storage\Tree\Node
     */
    public function getRoot()
    {
        return $this->root;
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
            if(!isset($leftNode)) {
                $leftNode = $node->getLeft();
            }

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
            $this->leftMost = $this->root;
            $this->rightMost = $this->root;
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
     * @param mixed $lower
     * @param mixed $higher
     * @return array
     * @throws \OutOfBoundsException
     */
    public function & findRange($lower, $higher)
    {
        $resultSet = [];

        if(null === $this->root) {
            return $resultSet;
        }

        if(!($this->comparator->compare($lower, $this->rightMost->getKey()) === AComparator::LESS)) {
            throw new \OutOfBoundsException("Lower value should be greater than right-most value or the tree");
        }

        if($this->comparator->compare($lower, $this->leftMost->getKey()) === AComparator::LESS) {
            $lower = $this->leftMost->getKey();
        }

        if($this->comparator->compare($higher, $this->rightMost->getKey()) === AComparator::GREATER) {
            $higher = $this->rightMost->getKey();
        }

        $resultSet = $this->findRangeRaw($lower, $higher, $this->root);

        return $resultSet;
    }

    /**
     * @param mixed $lower
     * @param mixed $higher
     * @param Node $node
     * @return array
     */
    protected function & findRangeRaw($lower, $higher, Node $node)
    {
        $resultSet = [];

        $leftNode = $node->getLeft();
        $rightNode = $node->getRight();

        if($leftNode instanceof Node && $node->getKey() > $lower) {
            $resultSet += $this->findRangeRaw($lower, $higher, $leftNode);
        }

        if($node->getKey() >= $lower && $node->getKey() <= $higher) {
            $resultSet[$node->getKey()] = $node;
        }

        if($rightNode instanceof Node && $node->getKey() < $higher) {
            $resultSet += $this->findRangeRaw($lower, $higher, $rightNode);
        }

        return $resultSet;
    }

    /**
     * @param mixed $value
     * @return array
     */
    public function & findGreater($value)
    {
        $resultSet = [];

        if(null === $this->root) {
            return $resultSet;
        }

        if(!($this->comparator->compare($value, $this->rightMost->getKey()) === AComparator::LESS)) {
            return $resultSet;
        }

        $resultSet = $this->findRange($value, $this->rightMost->getKey());

        uksort($resultSet, [$this->comparator, 'compare']);

        $firstNode = reset($resultSet);

        if($firstNode instanceof Node && $firstNode->getKey() === $value) {
            unset($resultSet[key($resultSet)]);
        }

        return $resultSet;
    }

    /**
     * @param mixed $value
     * @return array
     */
    public function & findLess($value)
    {
        $resultSet = [];

        if(null === $this->root) {
            return $resultSet;
        }

        if(!($this->comparator->compare($value, $this->leftMost->getKey()) === AComparator::GREATER)) {
            return $resultSet;
        }

        $resultSet = $this->findRange($this->leftMost->getKey(), $value);

        uksort($resultSet, [$this->comparator, 'compare']);

        $resultSet = array_reverse($resultSet, true);

        $firstNode = reset($resultSet);

        if($firstNode instanceof Node && $firstNode->getKey() === $value) {
            unset($resultSet[key($resultSet)]);
        }

        return $resultSet;
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
        if(null === $this->root) {
            return;
        }

        $rawValues = $this->mapTree($this->root);

        uksort($rawValues, [$this->comparator, 'compare']);

        $this->rightMost = end($rawValues);
        $this->leftMost = reset($rawValues);

        $this->persistByRawMiddle($rawValues);
    }

    /**
     * @param array $rawValues
     * @param Node $parent
     */
    protected function persistByRawMiddle(array & $rawValues, Node $parent = null)
    {
        $topIndex = floor(($rawValuesCount = count($rawValues)) / 2);

        reset($rawValues);
        for($i = 0; $rawValuesCount > 1 && $i < $topIndex; next($rawValues), $i++);
        $topKey = key($rawValues);

        /** @var Node $current */
        $current = $rawValues[$topKey];
        unset($rawValues[$topKey]);

        $current->setLeft();
        $current->setRight();

        if(null === $parent) {
            $current->setParent();
            $this->root = $current;
        } else {
            $current->setParent($parent);
        }

        if(!empty($rawValues)) {
            @list($left, $right) = @array_chunk($rawValues, $topIndex, true);

            if(null !== $left) {
                $leftIndex = floor(($rawLeftValuesCount = count($left)) / 2);

                reset($left);
                for($i = 0; $rawLeftValuesCount > 1 && $i < $leftIndex; next($left), $i++);
                $leftKey = key($left);

                /** @var Node $current */
                $leftNode = $rawValues[$leftKey];

                $current->setLeft($leftNode);

                $this->persistByRawMiddle($left, $current);
            }

            if(null !== $right) {
                $rightIndex = floor(($rawRightValuesCount = count($right)) / 2);

                reset($right);
                for($i = 0; $rawRightValuesCount > 1 && $i < $rightIndex; next($right), $i++);
                $rightKey = key($right);

                /** @var Node $current */
                $rightNode = $rawValues[$rightKey];

                $current->setright($rightNode);
                
                $this->persistByRawMiddle($right, $current);
            }
        }
    }

    /**
     * @param Node $node
     * @return array
     */
    protected function & mapTree(Node $node)
    {
        $resultSet = [];

        if(null !== ($leftNode = $node->getLeft())) {
            $resultSet += $this->mapTree($leftNode);
        }

        $resultSet[$node->getKey()] = $node;

        if(null !== ($rightNode = $node->getRight())) {
            $resultSet += $this->mapTree($rightNode);
        }

        return $resultSet;
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

    /**
     * @return string
     */
    public function _dump()
    {
        if($this->root instanceof Node) {
            return $this->root->_dump();
        }
    }
}