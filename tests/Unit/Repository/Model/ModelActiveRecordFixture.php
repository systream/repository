<?php

namespace Tests\Systream\Unit\Repository\Model;


use Systream\Repository\Model\ActiveRecordModelAbstract;
use Systream\Repository\Storage\NullStorage;
use Systream\Repository\Storage\StorageInterface;

/**
 * @property string foo
 * @property string bar
 */
class ModelActiveRecordFixture extends ActiveRecordModelAbstract
{
	public $propertyNotSet;

	/**
	 * @var StorageInterface
	 */
	static public $storage;

	/**
	 * @return StorageInterface
	 */
	public function getStorage()
	{
		return self::$storage ?: new NullStorage();
	}
}