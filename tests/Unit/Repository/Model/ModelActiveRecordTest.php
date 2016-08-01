<?php

namespace Tests\Systream\Unit\Repository\Model;


use Systream\Repository\Storage\StorageInterface;

class ModelActiveRecordTest extends ModelAbstractTest
{
	/**
	 * @return ModelActiveRecordFixture
	 */
	protected function getModel()
	{
		return new ModelActiveRecordFixture();
	}

	/**
	 * @test
	 */
	public function saveModel()
	{
		$model = $this->getModel();
		$storageMock = $this->getStorageMock();
		$storageMock->expects($this->atLeastOnce())->method('persist')->with($model);
		$model::$storage = $storageMock;
		$model->persist();
	}

	/**
	 * @test
	 */
	public function deleteModel()
	{
		$model = $this->getModel();
		$storageMock = $this->getStorageMock();
		$storageMock->expects($this->atLeastOnce())->method('purge')->with($model);
		$model::$storage = $storageMock;
		$model->purge();
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getStorageMock()
	{
		$storageMock = $this->getMockBuilder(StorageInterface::class)->getMock();
		return $storageMock;
	}


}