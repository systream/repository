<?php

namespace Systream\Repository\Storage\Query;


interface FilterInterface
{
	/**
	 * @return string
	 */
	public function getFieldName();

	/**
	 * @return string|int|float
	 */
	public function getValue();
}