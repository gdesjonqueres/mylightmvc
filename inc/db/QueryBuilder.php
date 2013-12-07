<?php
/**
 * Classe pour la construction de requêtes SQL
 *
 * @package Fw\Db\QueryBuilder
 */
class Db_QueryBuilder
{
	protected $_clauses;
	protected $_query;

	public function __construct()
	{
		$this->_clauses = array();
	}

	protected function _add($index, $value, $append = true)
	{
		if (!empty($value)) {
			if ($append) {
				if (!isset($this->_clauses[$index])) {
					$this->_clauses[$index] = array();
				}
				if (is_array($value)) {
					$this->_clauses[$index] = array_merge($this->_clauses[$index], $value);
				}
				else {
					$this->_clauses[$index][] = $value;
				}
			}
			else {
				if (is_array($value)) {
					$this->_clauses[$index] = $value;
				}
				else {
					$this->_clauses[$index] = array($value);
				}
			}
		}
	}

	protected function _remove($index)
	{
		unset($this->_clauses[$index]);
	}

	protected function _buildQuery()
	{
		$query = '';

		if (!empty($this->_clauses['select'])) {
			$query .= 'SELECT ' . implode(', ', $this->_clauses['select']);
		}
		if (!empty($this->_clauses['from'])) {
			$query .= "\n" . 'FROM ' . implode(', ', $this->_clauses['from']);
		}
		if (!empty($this->_clauses['join'])) {
			$query .= "\n" . implode("\n", $this->_clauses['join']);
		}
		if (!empty($this->_clauses['andWhere']) OR !empty($this->_clauses['orWhere'])) {
			$query .= "\n" . 'WHERE ';
			if (!empty($this->_clauses['andWhere'])) {
				$query .= implode("\n" . ' AND ', $this->_clauses['andWhere']);

			}
			if (!empty($this->_clauses['orWhere'])) {
				$or = implode(' OR ', $this->_clauses['orWhere']);
				if (!empty($this->_clauses['andWhere'])) {
					$query .= "\n" . ' AND (' . $or . ')';
				}
				else {
					$query .= $or;
				}
			}
		}
		if (!empty($this->_clauses['groupBy'])) {
			$query .= "\n" . 'GROUP BY ' . implode(', ', $this->_clauses['groupBy']);
		}
		if (!empty($this->_clauses['orderBy'])) {
			$query .= "\n" . 'ORDER BY ' . implode(', ', $this->_clauses['orderBy']);
		}
		if (!empty($this->_clauses['limit'])) {
			$query .= "\n" . 'LIMIT ' . implode(', ', $this->_clauses['limit']);
		}

		$this->_query = $query;
	}

	public function getClause($clause)
	{
		if (isset($this->_clauses[$clause])) {
			return $this->_clauses[$clause];
		}
		return null;
	}

	public function select($value)
	{
		$this->_add('select', $value);
		return $this;
	}

	public function from($value)
	{
		$this->_add('from', $value);
		return $this;
	}

	public function join($value)
	{
		$this->_add('join', $value);
		return $this;
	}

	public function where($value)
	{
		$this->_add('andWhere', $value, false);
		$this->_remove('orWhere');
		return $this;
	}

	public function andWhere($value)
	{
		$this->_add('andWhere', $value);
		return $this;
	}

	public function orWhere($value)
	{
		$this->_add('orWhere', $value);
		return $this;
	}

	public function groupBy($value)
	{
		$this->_add('groupBy', $value);
		return $this;
	}

	public function having($value)
	{
		$this->_add('andHaving', $value, false);
		$this->_remove('orHaving');
		return $this;
	}

	public function andHaving($value)
	{
		$this->_add('andHaving', $value);
		return $this;
	}

	public function orHaving($value)
	{
		$this->_add('orHaving', $value);
		return $this;
	}

	public function orderBy($value, $sort = 'ASC')
	{
		$this->_add('orderBy', "$value $sort");
		return $this;
	}

	public function limit($value, $value2 = null)
	{
		$this->_add('limit', (int) $value);
		if ($value2) {
			$this->_add('limit', (int) $value2);
		}
		return $this;
	}

	public function getQuery($force = false)
	{
		if (!isset($this->_query) || $force) {
			$this->_buildQuery();
		}
		return $this->_query;
	}
}