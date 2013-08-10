<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Helpers;


trait Singleton
{
    /**
     * @return self
     */
    public static function getInstance()
    {
        static $self;

        if(!($self instanceof self)) {
            $self = new self;
            call_user_func_array([$self, '__onAfterConstruct'], func_num_args());
        }

        return $self;
    }

    /**
     * Do something on class instantiating
     *
     * @return void
     */
    protected function __onAfterConstruct()
    {   }

    /**
     * Just do nothing
     *
     * {@inherit}
     */
    final private function __construct()
    {   }
}