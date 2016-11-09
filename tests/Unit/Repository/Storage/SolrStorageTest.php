<?php

namespace Tests\Systream\Unit\Repository\Storage;


use Systream\Repository\Storage\SolrStorage;
use Tests\Systream\Unit\Repository\Model\ModelFixture;

class SolrStorageTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function save_new()
	{
		$solrResultMock = $this->getMockBuilder('\Solarium\QueryType\Update\Result')
			->disableOriginalConstructor()
			->getMock();

		$solrResultMock
			->expects($this->any())
			->method('getStatus')
			->will($this->returnValue(0));

		$solrDocumentInterfaceMock = $this->getMockBuilder('Solarium\QueryType\Update\Query\Document\DocumentInterface')
			->getMock();

		$solrQueryMock = $this->getMockBuilder('\Solarium\QueryType\Update\Query\Query')
			->getMock();

		$solrQueryMock
			->expects($this->atLeastOnce())
			->method('createDocument')
			->will($this->returnValue($solrDocumentInterfaceMock));

		$client = $this->getQueryResultMock();
		$client
			->expects($this->any())
			->method('createUpdate')
			->will($this->returnValue($solrQueryMock));

		$client
			->expects($this->atLeastOnce())
			->method('update')
			->will($this->returnValue($solrResultMock));

		//$client->expects($this->atLeastOnce())->method('index');
		$storage = new SolrStorage($client, 'foo', 'bar');
		$model = new ModelFixture();
		$model->foo = 'test';
		$storage->persist($model);
		$this->assertFalse($model->isDirty());
	}

	/**
	 * @test
	 */
	public function save_update()
	{
		$solrResultMock = $this->getMockBuilder('\Solarium\QueryType\Update\Result')
			->disableOriginalConstructor()
			->getMock();

		$solrResultMock
			->expects($this->any())
			->method('getStatus')
			->will($this->returnValue(0));

		$solrDocumentInterfaceMock = $this->getMockBuilder('Solarium\QueryType\Update\Query\Document\DocumentInterface')
			->getMock();

		$solrQueryMock = $this->getMockBuilder('\Solarium\QueryType\Update\Query\Query')
			->getMock();

		$solrQueryMock
			->expects($this->atLeastOnce())
			->method('createDocument')
			->will($this->returnValue($solrDocumentInterfaceMock));

		$client = $this->getQueryResultMock();
		$client
			->expects($this->any())
			->method('createUpdate')
			->will($this->returnValue($solrQueryMock));

		$client
			->expects($this->atLeastOnce())
			->method('update')
			->will($this->returnValue($solrResultMock));

		//$client->expects($this->atLeastOnce())->method('index');
		$storage = new SolrStorage($client, 'foo', 'bar');
		$model = new ModelFixture();
		$model->foo = 'test';
		$model->id = 1;
		$storage->persist($model);
		$this->assertFalse($model->isDirty());
	}

	/**
	 * @test
	 */
	public function purge()
	{
		$solrResultMock = $this->getMockBuilder('\Solarium\QueryType\Update\Result')
			->disableOriginalConstructor()
			->getMock();

		$solrResultMock
			->expects($this->any())
			->method('getStatus')
			->will($this->returnValue(0));

		$solrQueryMock = $this->getMockBuilder('\Solarium\QueryType\Update\Query\Query')
			->getMock();

		$solrQueryMock
			->expects($this->atLeastOnce())
			->method('addDeleteById');

		$client = $this->getQueryResultMock();
		$client
			->expects($this->any())
			->method('createUpdate')
			->will($this->returnValue($solrQueryMock));

		$client
			->expects($this->atLeastOnce())
			->method('update')
			->will($this->returnValue($solrResultMock));

		$storage = new SolrStorage($client, 'foo', 'bar');
		$model = new ModelFixture();
		$model->foo = 'test';
		$model->id = 1;
		$model->markAsStored();
		$storage->purge($model);
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
	 * @return \PHPUnit_Framework_MockObject_MockObject|\Solarium\Client
	 */
	protected function getQueryResultMock()
	{
		return $this->getMockBuilder('Solarium\Client')
			//->setMethods(array('createDocument', 'createUpdate'))
			->getMock();
	}
}
