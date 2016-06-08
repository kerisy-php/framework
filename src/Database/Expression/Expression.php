<?php
namespace Kerisy\Database\Expression;

/**
 * An expression groups other expressions known as parts according to a type (AND, OR).
 * @author GeLo <geloen.eric@gmail.com>
 */
class Expression {
	/** @const string The AND expression type */
	const TYPE_AND = 'AND';

	/** @const string The OR expression type */
	const TYPE_OR = 'OR';

	/** @var string */
	private $type;

	/** @var array */
	private $parts;

	/**
	 * Expression constructor.
	 * @param string $type  The type (AND, OR).
	 * @param array  $parts The parts.
	 */
	public function __construct($type, array $parts = []) {
		$this->setType($type);
		$this->setParts($parts);
	}

	/**
	 * Gets the type.
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets the type.
	 * @param string $type The type.
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * Gets the parts.
	 * @return array
	 */
	public function getParts() {
		return $this->parts;
	}

	/**
	 * Sets the expression parts.
	 * @param array $parts The expression parts.
	 */
	public function setParts(array $parts) {
		$this->parts = [];

		foreach ($parts as $part) {
			$this->addPart($part);
		}
	}

	/**
	 * Adds a part to the expression.
	 * @param string|\Kerisy\Database\Expression\Expression $part The part to add to the expression.
	 */
	public function addPart($part) {
		$this->parts[] = $part;
	}

	/**
	 * Gets the string representation of the expression.
	 * @return string The string representation of the expression.
	 */
	public function __toString() {
		if (empty($this->parts)) {
			return '';
		}

		if (count($this->parts) === 1) {
			return (string)$this->parts[0];
		}

		return '(' . implode(') ' . $this->type . ' (', $this->parts) . ')';
	}
}
