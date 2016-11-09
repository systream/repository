<?php

namespace Systream\Repository\Storage\Query;


class RawSqlQuery implements QueryInterface
{
	/**
	 * @var string
	 */
	private $rawSql;

	/**
	 * @var array
	 */
	private $bindData;

	/**
	 * RawSqlQuery constructor.
	 * @param string $rawSql
	 * @param array $bindData
	 */
	public function __construct($rawSql, array $bindData = array())
	{
		$this->rawSql = $rawSql;
		$this->bindData = $bindData;
	}

	/**
	 * @return FilterInterface[]
	 */
	public function getFilters()
	{
		return array();
	}

	/**
	 * @return int|null
	 */
	public function getLimit()
	{
		return null;
	}

	/**
	 * @return int|null
	 */
	public function getOffset()
	{
		return null;
	}

	/**
	 * @return string
	 */
	public function getSql()
	{
		return $this->rawSql;
	}

	/**
	 * @return array
	 */
	public function getBindData()
	{
		return $this->bindData;
	}
}