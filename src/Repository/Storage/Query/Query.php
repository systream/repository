<?php

namespace Systream\Repository\Storage\Query;



class Query implements QueryInterface
{
	/**
	 * @var array
	 */
	private $filters = array();

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
}