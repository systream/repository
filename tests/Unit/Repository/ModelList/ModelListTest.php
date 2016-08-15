<?php

namespace Tests\Systream\Unit\Repository\ModelList;


use Systream\Repository\ModelList\ModelList;
use Tests\Systream\Unit\Repository\Model\ModelFixture;

class ModelListTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @tests
	 */
	public function emptyList()
	{
		$list = new ModelList();
		$this->assertNull($list->current());
	}

	/**
	 * @tests
	 */
	public function current()
	{
		$model1 = new ModelFixture();
		$model2 = new ModelFixture();
		$list = new ModelList();
		$list->addListItem($model1);
		$list->addListItem($model2);
		$this->assertSame($model1, $list->current());
	}

	/**
	 * @tests
	 */
	public function getNext()
	{
		$model1 = new ModelFixture();
		$model2 = new ModelFixture();
		$list = new ModelList();
		$list->addListItem($model1);
		$list->addListItem($model2);
		$list->next();
		$this->assertSame($model2, $list->current());
	}

	/**
	 * @tests
	 */
	public function afterLastElement()
	{
		$model1 = new ModelFixture();
		$model2 = new ModelFixture();
		$list = new ModelList();
		$list->addListItem($model1);
		$list->addListItem($model2);
		$list->next();
		$list->next();
		$this->assertNull($list->current());
	}

	/**
	 * @tests
	 */
	public function countTest()
	{
		$model1 = new ModelFixture();
		$model2 = new ModelFixture();
		$list = new ModelList();
		$list->addListItem($model1);
		$list->addListItem($model2);
		$this->assertEquals(2, $list->count());
		$this->assertEquals(2, count($list));
	}

	/**
	 * @tests
	 */
	public function countTest_emptyList()
	{
		$list = new ModelList();
		$this->assertEquals(0, $list->count());
		$this->assertEquals(0, count($list));
	}

	/**
	 * @tests
	 */
	public function iterate()
	{
		$model1 = new ModelFixture();
		$model2 = new ModelFixture();
		$list = new ModelList();
		$list->addListItem($model1);
		$list->addListItem($model2);

		$iterated = false;

		foreach ($list as $key => $item) {
			if ($key == 0) {
				$this->assertEquals($model1, $item);
			}

			if ($key == 1) {
				$this->assertEquals($model2, $item);
			}
			$iterated = true;
		}

		$this->assertTrue($iterated, 'List does not iterate');
	}

	/**
	 * @tests
	 */
	public function isEmpty_true()
	{
		$list = new ModelList();
		$this->assertTrue($list->isEmpty());
	}

	/**
	 * @tests
	 */
	public function isEmpty_false()
	{
		$list = new ModelList();
		$list->addListItem(new ModelFixture());
		$this->assertFalse($list->isEmpty());
	}
}
