<?php
namespace Kerisy\Database\Debug;

/**
 * Query debugger.
 * @author GeLo <geloen.eric@gmail.com>
 */
class QueryDebugger {
	/** @var string */
	private $query;

	/** @var array */
	private $parameters;

	/** @var array */
	private $types;

	/** @var float */
	private $time;

	/** @var float */
	private $start;

	/**
	 * Creates and starts a query debugger.
	 * @param string $query      The debugged query
	 * @param array  $parameters The debugged parameters.
	 * @param array  $types      The debugged types.
	 */
	public function __construct($query, array $parameters, array $types) {
		$this->query      = $query;
		$this->parameters = $parameters;
		$this->types      = $types;

		$this->start = microtime(true);
	}

	/**
	 * Stops the debug.
	 */
	public function stop() {
		$this->time = microtime(true) - $this->start;
	}

	/**
	 * Gets the debugged query.
	 * @return string The debugged query.
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Gets the debugged parameters.
	 * @return array The debugged parameters.
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * Gets the debugged types.
	 * @return array The debugged types.
	 */
	public function getTypes() {
		return $this->types;
	}

	/**
	 * Gets the execution time of the query in ms.
	 * @return float The execution time of the query.
	 */
	public function getTime() {
		return $this->time;
	}
}
