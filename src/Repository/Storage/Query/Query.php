<?php

namespace Systream\Repository\Storage\Query;



class Query implements QueryInterface
{
	/**
	 * @var array
	 */
	private $filters = array();

	/**
	 * @var int|null
	 */
	private $offset;

	/**
	 * @var int|null
	 */
	private $limit;

	/**
	 * @param FilterInterface $filter
	 */
	public function addFilter(FilterInterface $filter)
	{
		$this->filters[] = $filter;
	}

	/**
	 * @return FilterInterface[]
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 * @return int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @return int
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * @param int $offset
	 */
	public function setOffset($offset)
	{
		$this->offset = (int) $offset;
	}

	/**
	 * @param int $limit
	 */
	public function setLimit($limit)
	{
		$this->limit = (int) $limit;
	}
}