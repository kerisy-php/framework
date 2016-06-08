<?php
namespace Kerisy\Database\Event;

use Kerisy\Database\Debug\QueryDebugger;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when a query is debugged.
 * @author GeLo <geloen.eric@gmail.com>
 */
class QueryDebugEvent extends Event
{
    /** @var \Kerisy\Database\Debug\QueryDebugger */
    private $debugger;

    /**
     * Creates a debug query event.
     * @param \Kerisy\Database\Debug\QueryDebugger $debugger The query debugger.
     */
    public function __construct(QueryDebugger $debugger)
    {
        $this->debugger = $debugger;
    }

    /**
     * Gets the debugger.
     * @return \Kerisy\Database\Debug\QueryDebugger The query debugger.
     */
    public function getDebugger()
    {
        return $this->debugger;
    }
}
