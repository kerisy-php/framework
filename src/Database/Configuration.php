<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei
 * Date: 16/6/3
 * Time: 下午2:18
 */

namespace Kerisy\Database;


use Symfony\Component\EventDispatcher\EventDispatcher;

class Configuration
{
    /** Properties */
    protected $debug           = false;
    protected $eventDispatcher = null;
    protected $options         = [];
    protected $parameters      = [];

    /**
     * Creates a configuration.
     * @param boolean         $debug           The debug flag.
     * @param EventDispatcher $eventDispatcher The event dispatcher.
     */
    public function __construct($debug = false, EventDispatcher $eventDispatcher = null) {
        $this->setDebug($debug);
        $this->setEventDispatcher($eventDispatcher ?: new EventDispatcher());
    }

    /**
     * Gets the configuration debug flag.
     * @return boolean TRUE if the connection is debugged else FALSE.
     */
    public function getDebug() {
        return $this->debug;
    }

    /**
     * Sets the configuration debug flag.
     * @param boolean $debug TRUE if the connection is debugged else FALSE.
     * @return Configuration
     */
    public function setDebug($debug): Configuration {
        $this->debug = (bool)$debug;

        return $this;
    }

    /**
     * Gets the event dispatcher.
     * @return EventDispatcher The event dispatcher.
     */
    public function getEventDispatcher(): EventDispatcher {
        return $this->eventDispatcher;
    }

    /**
     * Sets the event dispatcher.
     * @param EventDispatcher $eventDispatcher The event dispatcher.
     * @return Configuration
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher): Configuration {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * Get the driver options
     * @return array The driver options.
     */
    public function getOptions(): array {
        return $this->options;
    }

    /**
     * Set the driver options
     * @param array|null $options The driver options (NULL to remove it).
     * @return Configuration
     */
    public function setOptions(array $options): Configuration {
        $this->options = $options;

        return $this;
    }

    /**
     * Get parameters
     * @return array
     */
    public function getParameters(): array {
        return $this->parameters;
    }

    /**
     * Checks if the connection has parameters.
     * @return boolean TRUE if the connection has parameters else FALSE.
     */
    public function hasParameters() {
        return !empty($this->getParameters());
    }

    /**
     * Checks if the connection has a parameter.
     * @param string $name The connection parameter name.
     * @return boolean TRUE if the connection has the parameter else FALSE.
     */
    public function hasParameter($name) {
        return isset($this->getParameters()[$name]);
    }

    /**
     * Gets a connection parameter.
     * @param string $name The connection parameter name.
     * @return mixed The connection parameter value.
     */
    public function getParameter($name) {
        return $this->hasParameter($name) ? $this->getParameters()[$name] : null;
    }

    /**
     * Set parameters
     * @param array $parameters
     * @return Configuration
     */
    public function setParameters(array $parameters): Configuration {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Sets a connection parameter.
     * @param string $name  The connection parameter name.
     * @param mixed  $value The connection parameter value (NULL to remove it).
     * @return Configuration
     */
    public function setParameter($name, $value): Configuration {
        if ($value !== null) {
            $this->getParameters()[$name] = $value;
        }
        else {
            unset($this->getParameters()[$name]);
        }

        return $this;
    }
}