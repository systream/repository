<?php

namespace Systream\Repository\Storage\Query;


class KeyValueFilter implements FilterInterface
{
	/**
	 * @var
	 */
	private $field;
	/**
	 * @var
	 */
	private $value;

	/**
	 * AndFilter constructor.
	 * @param string $field
	 * @param string $value
	 */
	public function __construct($field, $value)
	{
		$this->field = $field;
		$this->value = $value;
	}


	/**
	 * @param string $field
	 * @param string $value
	 * @return static|KeyValueFilter
	 */
	public static function create($field, $value)
	{
		return new static($field, $value);
	}

	/**
	 * @return string
	 */
	public function getFieldName()
	{
		return $this->field;
	}

	/**
	 * @return string|int|float
	 */
	public function getValue()
	{
		return $this->value;
	}
}