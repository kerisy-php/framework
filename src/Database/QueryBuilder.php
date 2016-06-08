<?php
namespace Kerisy\Database;


/**
 * A query builder allows to easily build a query.
 * @author GeLo <geloen.eric@gmail.com>
 */
use \Kerisy\Database\Connection as Connection;
class QueryBuilder {

	/** Constants */
	const SELECT          = 0;
	const INSERT          = 1;
	const UPDATE          = 2;
	const DELETE          = 3;
	const MODE_POSITIONAL = 0;
	const MODE_NAMED      = 1;

	/** Properties */
	protected $connection;
	protected $type              = self::SELECT;
	protected $mode              = self::MODE_POSITIONAL;
	protected $parts             = [
		'select'   => [],
		'from'     => [],
		'join'     => [],
		'set'      => [],
		'where'    => null,
		'group_by' => [],
		'having'   => null,
		'order_by' => [],
		'offset'   => null,
		'limit'    => null,
	];
	protected $parameters        = [];
	protected $parameterTypes    = [];
	protected $parameterCounters = [
		'positional' => 0,
		'named'      => [],
	];

	/**
	 * Query builder constructor.
	 * @param \Kerisy\Database\Connection $connection The query builder connection.
	 */
	public function __construct( $connection) {
		$this->connection = $connection;
	}

	/**
	 * Gets the query builder connection.
	 * @return \Kerisy\Database\Connection The query builder connection.
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * Gets the expression builder.
	 * @return \Kerisy\Database\Expression\ExpressionBuilder The expression builder.
	 */
	public function getExpressionBuilder() {
		return $this->getConnection()->getExpressionBuilder();
	}

	/**
	 * Gets the query builder type.
	 * @return integer The query builder type.
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Gets the query parameter mode.
	 * @return integer The query parameter mode.
	 */
	public function getMode() {
		return $this->mode;
	}

	/**
	 * Sets the query parameter mode.
	 * @param integer $mode The query parameter mode.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function setMode($mode) {
		$this->mode = $mode;

		return $this;
	}

	/**
	 * Gets the query parts.
	 * @return array The query parts.
	 */
	public function getParts() {
		return $this->parts;
	}

	/**
	 * Gets a query part.
	 * @param string $partName The query part name to retrieve.
	 * @return mixed The query part.
	 */
	public function getPart($partName) {
		return $this->parts[$partName];
	}

	/**
	 * Resets query parts.
	 * @param array $partNames The query part names to reset.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function resetParts(array $partNames = []) {
		if (empty($partNames)) {
			$partNames = array_keys($this->parts);
		}

		foreach ($partNames as $partName) {
			$this->resetPart($partName);
		}

		return $this;
	}

	/**
	 * Resets a query part.
	 * @param string $partName The query part name to reset.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function resetPart($partName) {
		$this->parts[$partName] = is_array($this->parts[$partName]) ? [] : null;

		return $this;
	}

	/**
	 * Sets the select query mode.
	 * @param string|array $selects The fields to select.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function select($selects = []) {
		$this->type = self::SELECT;

		if (!empty($selects)) {
			$this->parts['select'] = array_merge($this->parts['select'], (array)$selects);
		}

		return $this;
	}

	/**
	 * Sets the insert query mode for a specific table.
	 * @param string $insert The table name.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function insert($insert) {
		$this->type = self::INSERT;

		$this->from($insert);

		return $this;
	}

	/**
	 * Sets the update query mode for a specific table.
	 * @param string $update The table name.
	 * @param string $alias  The table alias.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function update($update, $alias = null) {
		$this->type = self::UPDATE;

		$this->from($update, $alias);

		return $this;
	}

	/**
	 * Sets the delete query mode for a specific table.
	 * @param string $delete The table name.
	 * @param string $alias  The table alias.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function delete($delete, $alias = null) {
		$this->type = self::DELETE;

		$this->from($delete, $alias);

		return $this;
	}

	/**
	 * Adds a "FROM" clause to the query.
	 * @param string $from  The table name.
	 * @param string $alias The table alias.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function from($from, $alias = null) {
		$this->parts['from'][] = [
			'table' => $from,
			'alias' => $alias,
		];

		return $this;
	}

	/**
	 * Adds an "INNER JOIN" clause to the query.
	 * @param string $fromAlias The from table alias.
	 * @param string $join      The join table name.
	 * @param string $alias     The join table alias.
	 * @param string $condition The join condition.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function innerJoin($fromAlias, $join, $alias, $condition = null) {
		return $this->join($fromAlias, 'inner', $join, $alias, $condition);
	}

	/**
	 * Adds a "LEFT JOIN" clause to the query.
	 * @param string $fromAlias The from table alias.
	 * @param string $join      The join table name.
	 * @param string $alias     The join table alias.
	 * @param string $condition The join condition.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function leftJoin($fromAlias, $join, $alias, $condition = null) {
		return $this->join($fromAlias, 'left', $join, $alias, $condition);
	}

	/**
	 * Adds a "RIGHT JOIN" clause to the query.
	 * @param string $fromAlias The from table alias.
	 * @param string $join      The join table name.
	 * @param string $alias     The join table alias.
	 * @param string $condition The join condition.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function rightJoin($fromAlias, $join, $alias, $condition = null) {
		return $this->join($fromAlias, 'right', $join, $alias, $condition);
	}

	/**
	 * Adds a "JOIN" clause to the query.
	 * @param string $fromAlias  The from table alias.
	 * @param string $type       The join type (inner, left, right).
	 * @param string $table      The join table name.
	 * @param string $alias      The join table alias.
	 * @param string $expression The join table expression.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function join($fromAlias, $type, $table, $alias, $expression = null) {
		if (!isset($this->parts['join'][$fromAlias])) {
			$this->parts['join'][$fromAlias] = [];
		}

		$this->parts['join'][$fromAlias][] = [
			'type'       => $type,
			'table'      => $table,
			'alias'      => $alias,
			'expression' => $expression,
		];

		return $this;
	}

	/**
	 * Sets a new field value for an insert/update query.
	 * @param string $identifier The identifier.
	 * @param mixed  $value      The value.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function set($identifier, $value) {
		$this->parts['set'][$identifier] = $value;

		return $this;
	}

	/**
	 * Adds a "WHERE" clause to the query.
	 * @param string|array|\Kerisy\Database\Expression\Expression $expression The expression.
	 * @param string                                                       $type       The expression type (AND, OR).
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function where($expression, $type = Expression\Expression::TYPE_AND) {
		return $this->addExpression('where', $type, $expression);
	}

	/**
	 * Adds an "AND (WHERE)" clause to the query.
	 * @param string|array|\Kerisy\Database\Expression\Expression $expression The expression.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function and ($expression) {
		return $this->addExpression('where', Expression\Expression::TYPE_AND, $expression);
	}

	/**
	 * Adds an "OR (WHERE)" clause to the query.
	 * @param string|array|\Kerisy\Database\Expression\Expression $expression The expression.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function or ($expression) {
		return $this->addExpression('where', Expression\Expression::TYPE_OR, $expression);
	}

	/**
	 * Adds a "GROUP BY" clause to the query.
	 * @param string|array $groupBy The group by clauses.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function groupBy($groupBy) {
		$this->parts['group_by'] = array_merge($this->parts['group_by'], (array)$groupBy);

		return $this;
	}

	/**
	 * Adds an "HAVING" clause to the query.
	 * @param string|array|\Kerisy\Database\Expression\Expression $expression The expression.
	 * @param string                                                       $type       The expression type (AND, OR).
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function having($expression, $type = Expression\Expression::TYPE_AND) {
		return $this->addExpression('having', $type, $expression);
	}

	/**
	 * Adds an "AND (HAVING)" clause to the query.
	 * @param string|array|\Kerisy\Database\Expression\Expression $expression The expression.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function andHaving($expression) {
		return $this->addExpression('having', Expression\Expression::TYPE_AND, $expression);
	}

	/**
	 * Adds an "OR (HAVING)" clause to the query.
	 * @param string|array|\Kerisy\Database\Expression\Expression $expression The expression.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function orHaving($expression) {
		return $this->addExpression('having', Expression\Expression::TYPE_OR, $expression);
	}

	/**
	 * Adds an "ORDER BY" clause to the query.
	 * @param string|array $orderBy The order by clauses.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function orderBy($orderBy) {
		$this->parts['order_by'] = array_merge($this->parts['order_by'], (array)$orderBy);

		return $this;
	}

	/**
	 * Sets the query offset.
	 * @param integer $offset The offset.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function offset($offset) {
		$this->parts['offset'] = $offset;

		return $this;
	}

	/**
	 * Sets the query limit.
	 * @param integer $limit The limit.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function limit($limit) {
		$this->parts['limit'] = $limit;

		return $this;
	}

	/**
	 * Sets query parameters.
	 * @param array $parameters The query parameters.
	 * @param array $types      The query parameter types.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function setParameters(array $parameters, array $types = []) {
		foreach ($parameters as $parameter => $value) {
			if (isset($types[$parameter])) {
				$this->setParameter($parameter, $value, $types[$parameter]);
			}
			else {
				$this->setParameter($parameter, $value);
			}
		}

		return $this;
	}

	/**
	 * Sets a query parameter.
	 * @param string $parameter The parameter.
	 * @param mixed  $value     The value.
	 * @param mixed  $type      The type.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	public function setParameter($parameter, $value, $type = null) {
		$this->parameters[$parameter] = $value;

		if ($type !== null) {
			$this->parameterTypes[$parameter] = $type;
		}

		if (is_int($parameter)) {
			$this->mode = self::MODE_POSITIONAL;
			$this->parameterCounters['positional'] ++;
		}
		else {
			$this->mode = self::MODE_NAMED;
		}

		return $this;
	}

	/**
	 * Creates and sets a parameter.
	 * @param mixed $value The value.
	 * @param mixed $type  The type.
	 * @return string The parameter placeholder.
	 */
	public function createParameter($value, $type = null) {
		if ($this->mode === self::MODE_POSITIONAL) {
			return $this->createPositionalParameter($value, $type);
		}

		return $this->createNamedParameter($value, $type);
	}

	/**
	 * Creates and sets a positional parameter.
	 * @param mixed $value The value.
	 * @param mixed $type  The type.
	 * @return string The positional parameter placeholder.
	 */
	public function createPositionalParameter($value, $type = null) {
		$this->setParameter($this->parameterCounters['positional'], $value, $type);

		return '?';
	}

	/**
	 * Creates and sets a named parameter.
	 * @param mixed  $value       The value.
	 * @param mixed  $type        The type
	 * @param string $placeholder The placeholder.
	 * @return string The named parameter placeholder.
	 */
	public function createNamedParameter($value, $type = null, $placeholder = null) {
		if ($placeholder === null) {
			$placeholder = ':fridge';
		}

		$parameter = substr($placeholder, 1);

		if (!isset($this->parameterCounters['named'][$parameter])) {
			$this->parameterCounters['named'][$parameter] = 0;
		}

		$placeholder = $placeholder . $this->parameterCounters['named'][$parameter];
		$this->setParameter($parameter . $this->parameterCounters['named'][$parameter], $value, $type);

		$this->parameterCounters['named'][$parameter] ++;

		return $placeholder;
	}

	/**
	 * Gets the query parameters.
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * Gets a query parameter.
	 * @param string $identifier The identifier.
	 * @return mixed The query parameter.
	 */
	public function getParameter($identifier) {
		return $this->parameters[$identifier] ?? null;
	}

	/**
	 * Gets the query parameter types.
	 * @return array The query parameter types.
	 */
	public function getParameterTypes() {
		return $this->parameterTypes;
	}

	/**
	 * Gets a query parameter type.
	 * @param string $identifier The identifier.
	 * @return mixed The query parameter type.
	 */
	public function getParameterType($identifier) {
		return $this->parameterTypes[$identifier] ?? null;
	}

	/**
	 * Gets the generated query.
	 * @return string The generated query.
	 */
	public function getQuery() {
		$query = null;
		switch ($this->type) {
			case self::SELECT:
				$query = $this->getSelectQuery();
				break;
			case self::INSERT:
				$query = $this->getInsertQuery();
				break;
			case self::UPDATE:
				$query = $this->getUpdateQuery();
				break;
			case self::DELETE:
				$query = $this->getDeleteQuery();
				break;
		}

		return $query;
	}

	/**
	 * Executes the query.
	 * @return mixed The result set in case of a "SELECT" query else the number of effected rows.
	 */
	public function execute() {
		if ($this->type === self::SELECT) {
			return $this->connection->executeQuery($this->getQuery(), $this->parameters, $this->parameterTypes);
		}

		return $this->connection->executeUpdate($this->getQuery(), $this->parameters, $this->parameterTypes);
	}

	/**
	 * Adds an expression to the "WHERE" or "HAVING" clause.
	 * @param string                                                       $part       The query part.
	 * @param string                                                       $type       The expression type (AND, OR)
	 * @param string|array|\Kerisy\Database\Expression\Expression $expression The expression.
	 * @return \Kerisy\Database\QueryBuilder The query builder.
	 */
	private function addExpression($part, $type, $expression) {
		if (!($expression instanceof Expression\Expression)) {
			$expression = new Expression\Expression($type, (array)$expression);
		}
		if ($this->parts[$part] === null) {
			$this->parts[$part] = $expression;

			return $this;
		}

		if ($this->parts[$part]->getType() !== $type) {
			$this->parts[$part] = new Expression\Expression($type, [$this->parts[$part]]);
		}

		foreach ($expression->getParts() as $expressionPart) {
			$this->parts[$part]->addPart($expressionPart);
		}

		return $this;
	}

	/**
	 * Generates a "SELECT" query
	 * @return string The "SELECT" query.
	 */
	private function getSelectQuery() {
		return 'SELECT ' . (empty($this->parts['select']) ? '*' : implode(', ', $this->parts['select'])) .
		' FROM ' . $this->getFromClause() .
		(($this->parts['where'] !== null) ? ' WHERE ' . $this->parts['where'] : null) .
		(!empty($this->parts['group_by']) ? ' GROUP BY ' . implode(', ', $this->parts['group_by']) : null) .
		(($this->parts['having'] !== null) ? ' HAVING ' . $this->parts['having'] : null) .
		(!empty($this->parts['order_by']) ? ' ORDER BY ' . implode(', ', $this->parts['order_by']) : null) .
		(($this->parts['limit'] !== null) ? ' LIMIT ' . $this->parts['limit'] : null) .
		(($this->parts['offset'] !== null) ? ' OFFSET ' . $this->parts['offset'] : null);
	}

	/**
	 * Generates an "INSERT" query.
	 * @return string The "INSERT" query.
	 */
	private function getInsertQuery() {
		return 'INSERT INTO ' . $this->parts['from'][0]['table'] .
		' (' . implode(', ', array_keys($this->parts['set'])) . ')' .
		' VALUES' .
		' (' . implode(', ', $this->parts['set']) . ')';
	}

	/**
	 * Generates an "UPDATE" query.
	 * @return string The "UPDATE" query.
	 */
	private function getUpdateQuery() {
		if (isset($this->parts['from'][0]['alias'])) {
			$fromClause = $this->parts['from'][0]['alias'] . ' FROM ' . $this->getFromClause();
		}
		else {
			$fromClause = $this->parts['from'][0]['table'];
		}

		$setClause = [];

		foreach ($this->parts['set'] as $idenfier => $value) {
			$setClause[] = $this->getExpressionBuilder()->equal($idenfier, $value);
		}

		return 'UPDATE ' . $fromClause .
		' SET ' . implode(', ', $setClause) .
		(($this->parts['where'] !== null) ? ' WHERE ' . $this->parts['where'] : null);
	}

	/**
	 * Generates a "DELETE" query.
	 * @return string The "DELETE" query.
	 */
	private function getDeleteQuery() {
		$fromClause = null;

		if (isset($this->parts['from'][0]['alias'])) {
			$fromClause = $this->parts['from'][0]['alias'] . ' ';
		}

		$fromClause .= 'FROM ' . $this->getFromClause();

		return 'DELETE ' . $fromClause .
		(($this->parts['where'] !== null) ? ' WHERE ' . $this->parts['where'] : null);
	}

	/**
	 * Generates the "FROM" clause.
	 * @return string The "FROM" clause.
	 */
	private function getFromClause() {
		$fromClauses = [];

		foreach ($this->parts['from'] as $from) {
			$fromClause = $from['table'];

			if ($from['alias'] !== null) {
				$fromClause .= ' ' . $from['alias'];
			}

			if (isset($this->parts['join'][$from['alias']])) {
				foreach ($this->parts['join'][$from['alias']] as $join) {
					$fromClause .= ' ' . strtoupper($join['type']) .
						' JOIN ' . $join['table'] . ' ' . $join['alias'] .
						' ON ' . $join['expression'];
				}
			}

			$fromClauses[] = $fromClause;
		}

		return implode(', ', $fromClauses);
	}
}
