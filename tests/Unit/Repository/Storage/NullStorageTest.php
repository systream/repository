<?php

namespace Tests\Systream\Storage;


use Systream\Repository\Storage\NullStorage;
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
}
