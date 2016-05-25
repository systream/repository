<?php

namespace Tests\Systream\Unit\Repository\Model;


class ModelAbstractTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @test
	 */
	public function instance()
	{
		$model = new ModelFixture();
		$this->assertInstanceOf('\Systream\Repository\Model\ModelAbstract', $model);
		$this->assertInstanceOf('\Systream\Repository\Model\ModelInterface', $model);
		$this->assertInstanceOf('\Systream\Repository\Model\SavableModelInterface', $model);
	}
	
	/**
	 * @test
	 * @param $data
	 * @dataProvider modelDataProvider
	 */
	public function loadData_getData($data)
	{
		$model = new ModelFixture();
		$model->loadData($data);
		$this->assertEquals($data, $model->getData());
		$this->assertEquals($data, $model->toArray());
	}

	/**
	 * @test
	 * @param $data
	 * @dataProvider modelDataProvider
	 */
	public function getViaMagic($data)
	{
		$model = new ModelFixture();
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
		$model = new ModelFixture();
		foreach ($data as $property => $value) {
			$model->$property = $value;
		}

		$this->assertEquals($data, $model->getData());
	}

	/**
	 * @test
	 */
	public function setExistsProperty()
	{
		$model = new ModelFixture();
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
		$model = new ModelFixture();
		$this->assertNull($model->anonexitsproperty);
	}

	/**
	 * @test
	 */
	public function defaultIsDirty()
	{
		$model = new ModelFixture();
		$this->assertTrue($model->isDirty());
	}

	/**
	 * @test
	 */
	public function removeDirtyFlagByMarkAsStored()
	{
		$model = new ModelFixture();
		$this->assertTrue($model->isDirty());
		$model->markAsStored();
		$this->assertFalse($model->isDirty());
	}

	/**
	 * @test
	 */
	public function setDirtyByModifyProperty()
	{
		$model = new ModelFixture();;
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
		$model = new ModelFixture();
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
		$model = new ModelFixture();
		$model->loadData($data);
		$this->assertEquals(array_keys($data), $model->getFields());
	}

	/**
	 * @test
	 */
	public function originalValue_nothing()
	{
		$model = new ModelFixture();
		$model->foo = 'bar';
		$this->assertNull($model->getOriginalValue('foo'));
	}

	/**
	 * @test
	 */
	public function originalValue_something()
	{
		$model = new ModelFixture();
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
		$model = new ModelFixture();
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
		$model = new ModelFixture();
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
					'bar' => new ModelFixture()
				)
			),
		);
	}

}