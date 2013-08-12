<?php
/**
 * @author AlexanderC
 */

class Timer 
{
    /**
     * @var float
     */
    protected $start;

    /**
     * @var float
     */
    protected $time = 0;

    /**
     * @return void
     */
    public function start()
    {
        $this->start = microtime(true);
    }

    /**
     * @return void
     */
    public function stop()
    {
        $this->time += microtime(true) - $this->start;
    }

    /**
     * @return float
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param string|bool $format
     * @return string
     */
    public function format($format = false)
    {
        return sprintf($format ? : "%01.4f", $this->time);
    }
}