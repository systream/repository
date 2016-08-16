<?php

namespace Tests\Systream\Storage;


use SebastianBergmann\CodeCoverage\Filter;
use Systream\Repository\Storage\NullStorage;
use Systream\Repository\Storage\Query\KeyValueFilter;
use Systream\Repository\Storage\Query\Query;
use Tests\Systream\Unit\Repository\Model\ModelFixture;

class NullStorageTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @test
	 */
	public function dirtyPersist()
	{
		$storage = new NullStorage();

		$model = new ModelFixture();
		$model->foo = 1;
		$model->markAsStored();

		$storage->persist($model);
	}

	/**
	 * @test
	 */
	public function markAsStored()
	{
		$storage = new NullStorage();

		$model = new ModelFixture();
		$model->foo = 1;

		$storage->persist($model);
		$this->assertFalse($model->isDirty());
	}

	/**
	 * @test
	 * @expectedException \Systream\Repository\Storage\Exception\DirtyModelException
	 */
	public function dirtyCannotDeleted()
	{
		$storage = new NullStorage();

		$model = new ModelFixture();
		$model->foo = 1;

		$storage->purge($model);
	}

	/**
	 * @test
	 */
	public function find()
	{
		$storage = new NullStorage();
		$query = new Query();
		$query->addFilter(new KeyValueFilter('foo', 'bar'));
		$storage->find($query, new ModelFixture());
	}

	/**
	 * @test
	 */
	public function find_offsetLimit()
	{
		$storage = new NullStorage();
		$query = new Query();
		$query->setOffset(10);
		$query->setLimit(1000);
		$query->addFilter(new KeyValueFilter('foo', 'bar'));
		$storage->find($query, new ModelFixture());
	}
}