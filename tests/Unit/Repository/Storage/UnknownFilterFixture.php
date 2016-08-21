<?php

namespace Tests\Systream\Unit\Repository\Storage;


use Systream\Repository\Storage\Query\FilterInterface;

class UnknownFilterFixture implements FilterInterface
{

	/**
	 * @return string
	 */
	public function getFieldName()
	{
		return 'bar';
	}

	/**
	 * @return string|int|float
	 */
	public function getValue()
	{
		return 'fuu';
	}
}