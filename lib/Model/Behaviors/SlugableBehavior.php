<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Model\Behaviors;


use PStorage\AModel;

class SlugableBehavior extends ABehavior
{
    const DEFAULT_HOLDER = 'slug';
    const DEFAULT_SLUG = 'n-a';

    /**
     * @var string
     */
    protected $property;

    /**
     * @var string
     */
    protected $holder;

    /**
     * @param AModel $model
     * @throws \RuntimeException
     * @return void
     */
    function apply(AModel $model)
    {
        $properties = $model->getDefinition()->getAllPropertiesExceptPrimaryKey();

        if(!in_array($this->property, $properties)) {
            throw new \RuntimeException("Slugable Behavior property missing in " . get_class($model) . " model");
        } else if(!in_array($this->holder, $properties)) {
            throw new \RuntimeException("Slugable Behavior holder missing in " . get_class($model) . " model");
        }

        $model->{$this->holder} = self::slugify($model->{$this->property});

        // case required unique value
        if(in_array($this->holder, $model->getDefinition()->getUniqueProperties())) {
            $locatorMethod = "findOneBy{$this->holder}";

            while(false !== call_user_func([$model, $locatorMethod], $model->{$this->holder})) {
                $model->{$this->holder} .= "-" . sha1(microtime(true));
            }
        }
    }

    /**
     * @param array $configuration
     * @throws \RuntimeException
     */
    function init(array $configuration)
    {
        if(!isset($configuration['property'])) {
            throw new \RuntimeException("Slugable Behavior requires a property name to be defined");
        }

        $this->property = (string) $configuration['property'];
        $this->holder = isset($configuration['holder']) ? $configuration['holder'] : self::DEFAULT_HOLDER;
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
    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * @return int
     */
    static function getType()
    {
        return self::TYPE_PRE;
    }

    /**
     * @param string $string
     * @return string
     */
    protected static function slugify($string)
    {
        $string = preg_replace('~[^\\pL\d]+~u', '-', $string);
        $string = trim($string, '-');
        $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
        $string = strtolower($string);
        $string = preg_replace('~[^-\w]+~', '', $string);

        if (empty($string)){
            return self::DEFAULT_SLUG;
        }
        return $string;
    }
}