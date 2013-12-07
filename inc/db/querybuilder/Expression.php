<?php
/**
 * Classe pour la construction des expressions dans une requête SQL
 * @package Fw\Db\QueryBuilder
 */
class Db_QueryBuilder_Expression
{
	public function eq($key, $value)
	{
		return "$key = $value";
	}

	public function notNull($key)
	{
		return "$key IS NOT NULL";
	}

	public function in($key, array $values, $callback = NULL)
	{
		$v = $callback ? $this->_executeCallback($callback, $values) : $values;
		return "$key IN (" . implode(', ', $v) . ')';
	}

	protected function _executeCallback($callback, $values)
	{
		if (is_array($values)) {
			return array_map($callback, $values);
		}
		else {
			return call_user_func($callback, $values);
		}
	}
}