<?php
namespace Kerisy\Database\Event;

use Kerisy\Database\Connection;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched just after a connection has been established.
 * @author GeLo <geloen.eric@gmail.com>
 */
class PostConnectEvent extends Event
{
    /** @var \Kerisy\Database\Connection */
    private $connection;

    /**
     * Creates a post connect event.
     * @param \Kerisy\Database\Connection $connection The connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Gets the connection
     * @return \Kerisy\Database\Connection The connection.
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
