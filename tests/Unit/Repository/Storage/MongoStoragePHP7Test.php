<?php

namespace Tests\Systream\Unit\Repository\Storage;


class MongoStoragePHP7Test extends MongoStoragePHP5Test
{
	const SAVE_METHOD = 'insertOne';
	const REMOVE_METHOD = 'deleteOne';
	const FIND_METHOD = 'find';

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|\MongoCollection
	 */
	protected function getMongoCollectionMock()
	{
		$mongoCollection = $this->getMockBuilder('\\MongoDB\\Collection')
			->setMethods(array('insertOne', 'deleteOne', 'find'))
			->disableOriginalConstructor()
			->getMock();
		return $mongoCollection;
	}
}
