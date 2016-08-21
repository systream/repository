<?php

namespace Tests\Systream\Unit\Repository\Storage;


use Systream\Repository\Storage\MongoStorage;
use Systream\Repository\Storage\Query\KeyValueFilter;
use Systream\Repository\Storage\Query\Query;
use Tests\Systream\Unit\Repository\Model\ModelFixture;

class SolrStorageTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @test
	 */
	public function save()
	{

	}

	/**
	 * @test
	 */
	public function purge()
	{

	}

	/**
	 * @test
	 */
	public function find_empty()
	{

	}

	/**
	 * @test
	 */
	public function find_oneKeyValue()
	{

	}

	/**
	 * @test
	 */
	public function find_moreKeyValue()
	{

	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getQueryResultMock()
	{
		return $this->getMockBuilder('Solarium\QueryType\MoreLikeThis\Result')
		->setMethods(
			array(
				'getQuery',
				'parseResponse',
			)
		);
	}
}
