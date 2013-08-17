<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Model\Behaviors\Helpers;


use PStorage\Model\Behaviors\ABehavior;

trait BehaviorApplyable
{
    /**
     * @var array
     */
    protected $preBehaviors = [];

    /**
     * @var array
     */
    protected $postBehaviors = [];

    /**
     * @return array
     */
    public function getPostBehaviors()
    {
        return $this->postBehaviors;
    }

    /**
     * @return array
     */
    public function getPreBehaviors()
    {
        return $this->preBehaviors;
    }

    /**
     * @param ABehavior $behavior
     */
    public function addPreBehavior(ABehavior $behavior)
    {
        $this->preBehaviors[spl_object_hash($behavior)] = $behavior;
    }

    /**
     * @param ABehavior $behavior
     */
    public function addPostBehavior(ABehavior $behavior)
    {
        $this->postBehaviors[spl_object_hash($behavior)] = $behavior;
    }

    /**
     * @param ABehavior $behavior
     */
    public function removePreBehavior(ABehavior $behavior)
    {
        unset($this->preBehaviors[spl_object_hash($behavior)]);
    }

    /**
     * @param ABehavior $behavior
     */
    public function removePostBehavior(ABehavior $behavior)
    {
        unset($this->postBehaviors[spl_object_hash($behavior)]);
    }

    /**
     * @return void
     */
    protected function importBehaviors()
    {
        foreach($this->behaviors() as $behaviorName => $configuration) {
            $behaviorClass = sprintf("PStorage\\Model\\Behaviors\\%sBehavior", ucfirst($behaviorName));
            /** @var ABehavior $behavior */
            $behavior = new $behaviorClass;

            if(!($behavior instanceof ABehavior)) {
                throw new \RuntimeException("Behavior should be instance of ABehavior");
            }

            $behavior->init($configuration);

            $behaviorType = call_user_func("{$behaviorClass}::getType");

            if(!in_array($behaviorType, [ABehavior::TYPE_PRE, ABehavior::TYPE_POST])) {
                throw new \RuntimeException("Behavior type should be both TYPE_PRE or TYPE_POST");
            }

            if(ABehavior::TYPE_PRE === $behaviorType) {
                $this->addPreBehavior($behavior);
            } else {
                $this->addPostBehavior($behavior);
            }
        }
    }

    /**
     * @return array
     */
    abstract protected function behaviors();
}