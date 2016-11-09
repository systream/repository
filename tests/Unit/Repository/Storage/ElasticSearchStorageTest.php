<?php

namespace Tests\Systream\Unit\Repository\Storage;


use Systream\Repository\Storage\ElasticSearchStorage;
use Tests\Systream\Unit\Repository\Model\ModelFixture;

class ElasticSearchStorageTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @test
	 */
	public function save()
	{
		$client = $this->getQueryResultMock();
		$client->expects($this->atLeastOnce())->method('index');
		$storage = new ElasticSearchStorage($client, 'foo', 'bar');
		$model = new ModelFixture();
		$model->foo = 'test';
		$storage->persist($model);
		$this->assertFalse($model->isDirty());
	}

	/**
	 * @test
	 */
	public function purge()
	{
		$client = $this->getQueryResultMock();
		$client->expects($this->atLeastOnce())->method('update');
		$storage = new ElasticSearchStorage($client, 'foo', 'bar');
		$model = new ModelFixture();
		$model->foo = 'test';
		$model->id = 10;
		$model->markAsStored();
		$model->foo = 'test2';
		$storage->persist($model);
		$this->assertFalse($model->isDirty());

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
	 * @return \PHPUnit_Framework_MockObject_MockObject|\Elasticsearch\Client
	 */
	protected function getQueryResultMock()
	{
		return $this->getMockBuilder('\Elasticsearch\Client')
			->setMethods(array('index', 'update'))
			->disableOriginalConstructor()
			->getMock();
	}
}
