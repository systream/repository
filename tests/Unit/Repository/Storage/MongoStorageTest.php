<?php

namespace Tests\Systream\Storage;


use Systream\Repository\Storage\MongoStorage;
use Tests\Systream\Unit\Repository\Model\ModelFixture;

class MongoStorageTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @test
	 */
	public function save()
	{
		$mongoCollection = $this->getMockBuilder('\MongoCollection')
			->disableOriginalConstructor()
			->setMethods(array('save'))
			->getMock();

		$mongoCollection->expects($this->once())->method('save');
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
		$mongoCollection = $this->getMockBuilder('\MongoCollection')
			->disableOriginalConstructor()
			->setMethods(array('remove'))
			->getMock();

		$mongoCollection->expects($this->once())->method('remove');
		$mongoStorage = new MongoStorage($mongoCollection);
		$model = new ModelFixture();
		$model->bar = 'foo';
		$model->foo = 'bar';
		$mongoStorage->purge($model);
		$this->assertFalse($model->isDirty());
	}
}
