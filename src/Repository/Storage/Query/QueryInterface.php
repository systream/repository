<?php

namespace Systream\Repository\Storage\Query;

interface QueryInterface
{
	/**
	 * @return FilterInterface[]
	 */
	public function getFilters();

	/**
	 * @return int
	 */
	public function getLimit();

	/**
	 * @return int
	 */
	public function getOffset();
}