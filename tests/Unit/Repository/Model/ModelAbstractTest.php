<?php

namespace Tests\Systream\Unit\Repository\Model;


use Systream\Repository\ModelList\ModelList;

class ModelAbstractTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @test
	 */
	public function instance()
	{
		$model = $this->getModel();
		$this->assertInstanceOf('\Systream\Repository\Model\ModelAbstract', $model);
		$this->assertInstanceOf('\Systream\Repository\Model\ModelInterface', $model);
		$this->assertInstanceOf('\Systream\Repository\Model\SavableModelInterface', $model);
	}
	
	/**
	 * @test
	 * @param $data
	 * @dataProvider modelDataProvider
	 */
	public function loadData_getData($data, $expected = null)
	{
		$model = $this->getModel();
		$model->loadData($data);
		if (!$expected) {
			$expected = $data;
		}
		$this->assertEquals($expected, $model->getData());
		$this->assertEquals($expected, $model->toArray());
	}

	/**
	 * @test
	 */
	public function toArray_Model()
	{
		$fixture = $this->getModel();
		$fixture->foo = '2bar';
		$fixture->bar = '2foo';

		$model = $this->getModel();
		$model->bar = 'foo';
		$model->foo = 'bar';
		$model->fixture = $fixture;

		$this->assertEquals(
			array(
				'bar' => 'foo',
				'foo' => 'bar',
				'fixture' => array(
					'foo' => '2bar',
					'bar' => '2foo'
				)
			),
			$model->toArray()
		);
	}

	/**
	 * @test
	 */
	public function toArray_ModelList()
	{
		$fixture = $this->getModel();
		$fixture->foo = '2bar';
		$fixture->bar = '2foo';

		$fixture2 = $this->getModel();
		$fixture2->foo = '3bar';
		$fixture2->bar = '3foo';

		$modelList = new ModelList();
		$modelList->addListItem($fixture);
		$modelList->addListItem($fixture2);

		$model = $this->getModel();
		$model->bar = 'foo';
		$model->foo = 'bar';
		$model->fixture = $modelList;

		$this->assertEquals(
			array(
				'bar' => 'foo',
				'foo' => 'bar',
				'fixture' => array(
					array(
						'foo' => '2bar',
						'bar' => '2foo'
					),
					array(
						'foo' => '3bar',
						'bar' => '3foo'
					),
				)
			),
			$model->toArray()
		);
	}

	/**
	 * @test
	 * @param $data
	 * @dataProvider modelDataProvider
	 */
	public function getViaMagic($data)
	{
		$model = $this->getModel();
		$model->loadData($data);
		foreach ($data as $property => $value) {
			$this->assertEquals($value, $model->$property);
		}
	}

	/**
	 * @test
	 * @param $data
	 * @dataProvider modelDataProvider
	 */
	public function setViaMagic($data)
	{
		$model = $this->getModel();
		foreach ($data as $property => $value) {
			$model->$property = $value;
		}

		foreach ($data as $property => $value) {
			$this->assertEquals($value, $model->$property);
		}
	}

	/**
	 * @test
	 */
	public function setExistsProperty()
	{
		$model = $this->getModel();
		$model->propertyNotSet = 'foo';

		$this->assertEquals('foo', $model->propertyNotSet);

		$modelData = $model->getData();
		$this->assertFalse(isset($modelData['foo']));
	}

	/**
	 * @test
	 */
	public function getPropertyNotSetNotExists_viaMagic()
	{
		$model = $this->getModel();
		$this->assertNull($model->anonexitsproperty);
	}

	/**
	 * @test
	 */
	public function defaultIsDirty()
	{
		$model = $this->getModel();
		$this->assertTrue($model->isDirty());
	}

	/**
	 * @test
	 */
	public function removeDirtyFlagByMarkAsStored()
	{
		$model = $this->getModel();
		$this->assertTrue($model->isDirty());
		$model->markAsStored();
		$this->assertFalse($model->isDirty());
	}

	/**
	 * @test
	 */
	public function setDirtyByModifyProperty()
	{
		$model = $this->getModel();;
		$model->markAsStored();
		$this->assertFalse($model->isDirty());
		$model->foo = 'bar';
		$this->assertTrue($model->isDirty());
	}

	/**
	 * @test
	 */
	public function dirtyFlagRemainsTheSameWhenTheSameValueSetted()
	{
		$model = $this->getModel();
		$model->foo = 'bar';
		$model->markAsStored();
		$this->assertFalse($model->isDirty());
		$model->foo = 'bar';
		$this->assertFalse($model->isDirty());
	}

	/**
	 * @test
	 * @param $data
	 * @dataProvider modelDataProvider
	 */
	public function getOriginalValue($data)
	{
		$model = $this->getModel();
		$model->loadData($data);
		$this->assertEquals(array_keys($data), $model->getFields());
	}

	/**
	 * @test
	 */
	public function originalValue_nothing()
	{
		$model = $this->getModel();
		$model->foo = 'bar';
		$this->assertNull($model->getOriginalValue('foo'));
	}

	/**
	 * @test
	 */
	public function originalValue_something()
	{
		$model = $this->getModel();
		$model->foo = 'bar';
		$model->markAsStored();
		$model->foo = 'bar2';
		$this->assertEquals('bar', $model->getOriginalValue('foo'));
	}

	/**
	 * @test
	 */
	public function originalValue_doubleSet()
	{
		$model = $this->getModel();
		$model->foo = 'bar';
		$model->markAsStored();
		$model->foo = 'bar2';
		$model->foo = 'bar3';
		$this->assertEquals('bar', $model->getOriginalValue('foo'));
	}

	/**
	 * @test
	 */
	public function originalValue_sameWhenNotModified()
	{
		$model = $this->getModel();
		$model->foo = 'bar';
		$model->markAsStored();
		$this->assertEquals(
			'bar', $model->getOriginalValue('foo')
		);

		$this->assertEquals('bar', $model->foo );
	}

	/**
	 * @return array
	 */
	public function modelDataProvider()
	{
		return array(
			array(
				array(
					'foo' => 'bar',
					'bar' => 'foo'
				)
			),

			array(
				array(
					 0 => 'bar',
					10 => 'foo'
				)
			),

			array(
				array(
					'foo', 'bar', 'fooBar'
				)
			),

			array(
				array(
					0 => 'bar',
					10 => array('foo' => 'bar')
				)
			),

			array(
				array(
					'fooo' => 'ááéűáéűá',
					'bar' => new \stdClass()
				)
			),

			array(
				array(
					'fooo' => 'ááéűáéűá',
					'f2' => null,
					'bar' => new \stdClass(),
					'óüüüóáááápűőá' => true,
				)
			),

			array(
				array(
					0 => false,
				)
			),

			array(
				array(
					'bar' => new ModelFixture()
				),
				array(
					'bar' => array()
				)
			),
		);
	}

	/**
	 * @return ModelFixture
	 */
	protected function getModel()
	{
		return new ModelFixture();
	}

}
