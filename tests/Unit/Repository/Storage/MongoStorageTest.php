<?php

namespace Tests\Systream\Unit\Repository\Storage;


use Systream\Repository\Storage\Exception\NotSupportedFilterException;
use Systream\Repository\Storage\MongoStorage;
use Systream\Repository\Storage\Query\KeyValueFilter;
use Systream\Repository\Storage\Query\Query;
use Tests\Systream\Unit\Repository\Model\ModelFixture;

class MongoStorageTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function save()
	{
		$mongoCollection = $this->getMongoCollectionMock();
		$mongoCollection
			->expects($this->once())
			->method('save');

		$mongoStorage = new MongoStorage($mongoCollection);
		$model = new ModelFixture();
		$model->bar = 'foo';
		$model->foo = 'bar';
		$mongoStorage->persist($model);

		$this->assertFalse($model->isDirty());
	}

	/**
	 * @test
	 */
	public function purge()
	{
		$mongoCollection = $this->getMongoCollectionMock();

		$mongoCollection
			->expects($this->once())
			->method('remove');

		$mongoStorage = new MongoStorage($mongoCollection);
		$model = new ModelFixture();
		$model->bar = 'foo';
		$model->foo = 'bar';
		$mongoStorage->purge($model);
		$this->assertFalse($model->isDirty());
	}

	/**
	 * @test
	 */
	public function find_empty()
	{
		$mongoCollection = $this->getMongoCollectionMock();

		$mongoCursor = $this->getMockBuilder('\MongoCursor')
			->disableOriginalConstructor()
			->setMethods(array('find'))
			->getMock();

		$mongoCollection
			->expects($this->once())
			->method('find')
			->with($this->equalTo(array()))
			->will($this->returnValue($mongoCursor));

		$mongoStorage = new MongoStorage($mongoCollection);
		$model = new ModelFixture();
		$query = new Query();
		$mongoStorage->find($query, $model);
	}

	/**
	 * @test
	 */
	public function find_oneKeyValue()
	{
		$mongoCollection = $this->getMongoCollectionMock();

		$mongoCollection
			->expects($this->once())
			->method('find')
			->with($this->equalTo(array('foo' => 'bar')))
			->will($this->returnValue(array(
				array('foo' => 'bar', 'bar' => 'foo', 'id' => 10, '_id' => 1000),
				array('foo' => 'baasdasdr', 'bar' => 'foasdfaso', 'id' => 11, '_id' => 1001),
			)));

		$mongoStorage = new MongoStorage($mongoCollection);
		$model = new ModelFixture();
		$query = new Query();
		$query->addFilter(KeyValueFilter::create('foo', 'bar'));
		$modelList = $mongoStorage->find($query, $model);
		$this->assertEquals(2, $modelList->count());
	}

	/**
	 * @test
	 */
	public function find_moreKeyValue()
	{
		$mongoCollection = $this->getMongoCollectionMock();

		$mongoCollection
			->expects($this->once())
			->method('find')
			->with($this->equalTo(array('foo' => 'bar', 'bar' => 10)))
			->will($this->returnValue(array(array('foo' => 'bar'))));

		$mongoStorage = new MongoStorage($mongoCollection);
		$model = new ModelFixture();
		$query = new Query();
		$query->addFilter(KeyValueFilter::create('foo', 'bar'));
		$query->addFilter(KeyValueFilter::create('bar', 10));
		$mongoStorage->find($query, $model);
	}

	/**
	 * @test
	 */
	public function unknownFilter()
	{
		$this->expectException(NotSupportedFilterException::class);
		$mongoCollection = $this->getMockBuilder('\MongoCollection')
			->disableOriginalConstructor()
			->getMock();
		$storage = new MongoStorage($mongoCollection);
		$model = new ModelFixture();
		$query = new Query();
		$query->addFilter(new UnknownFilterFixture());
		$storage->find($query, $model);
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|\MongoCollection
	 */
	protected function getMongoCollectionMock()
	{
		$mongoCollection = $this->getMockBuilder('\\MongoCollection')
			->setMethods(array('save', 'remove', 'find'))
			->disableOriginalConstructor()
			->getMock();
		return $mongoCollection;
	}
}
