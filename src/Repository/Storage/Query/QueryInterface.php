<?php

namespace Systream\Repository\Storage\Query;

interface QueryInterface
{
	/**
	 * @return FilterInterface[]
	 */
	public function getFilters();

	/**
	 * @return int|null
	 */
	public function getLimit();

	/**
	 * @return int|null
	 */
	public function getOffset();
}