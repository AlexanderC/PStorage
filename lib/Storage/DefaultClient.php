<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage;


use PStorage\Helpers\Singleton;

class DefaultClient
{
    use Singleton;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __onAfterConstruct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param \PStorage\Storage\Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return \PStorage\Storage\Client
     */
    public function getClient()
    {
        return $this->client;
    }
}