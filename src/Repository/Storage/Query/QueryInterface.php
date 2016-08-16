<?php

namespace Systream\Repository\Storage\Query;

interface QueryInterface
{
	/**
	 * @return FilterInterface[]
	 */
	public function getFilters();
}