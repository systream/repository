<?php

namespace Tests\Systream\Storage;


use Systream\Repository\Storage\SqlStorage;
use Tests\Systream\Unit\Repository\Model\ModelFixture;

class SqlStorageTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var \PDO
	 */
	protected $pdo;

	/**
	 * @return \PDO
	 */
	public function getPDO()
	{
		if (!$this->pdo) {
			$this->pdo = new \PDO('sqlite::memory:');
			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		}
		return $this->pdo;
	}

	/**
	 * @test
	 */
	public function donTSaveNotDirtyModel()
	{
		$pdo = $this->getPDO();

		$this->createTestTable($pdo);

		$storage = new SqlStorage($pdo, 'test');
		$model = new ModelFixture();
		$model->foo = 'test';
		$model->markAsStored();
		$storage->persist($model);

		$this->assertEmpty(
			$this->getFormSql($pdo, 'select * from test')
		);
	}

	/**
	 * @test
	 */
	public function storeNewModel()
	{
		$pdo = $this->getPDO();

		$this->createTestTable($pdo);

		$storage = new SqlStorage($pdo, 'test');
		$model = new ModelFixture();
		$model->foo = 'test';
		$model->bar = 10;
		$storage->persist($model);

		$this->assertEquals(
			array(
				array(
					'id' => 1,
					'foo' => 'test',
					'bar' => 10
				)
			),
			$this->getFormSql($pdo, 'select * from test')
		);

		$this->assertFalse($model->isDirty());
		$this->assertEquals(1, $model->id, 'Save does not set new id');
	}


	/**
	 * @test
	 * @depends storeNewModel
	 */
	public function storeExistsModel()
	{
		$this->storeNewModel();
		$pdo = $this->getPDO();

		$storage = new SqlStorage($pdo, 'test');
		$model = new ModelFixture();
		$model->foo = 'test';
		$model->id = 1;
		$model->markAsStored();

		$model->foo = 'bar';
		$model->bar = 15;
		$storage->persist($model);

		$this->assertEquals(
			array(
				array(
					'id' => 1,
					'foo' => 'bar',
					'bar' => 15
				)
			),
			$this->getFormSql($pdo, 'select * from test')
		);

		$this->assertFalse($model->isDirty());
	}

	/**
	 * @test
	 * @depends storeNewModel
	 */
	public function deleteExistsModel()
	{
		$this->storeNewModel();
		$pdo = $this->getPDO();

		$storage = new SqlStorage($pdo, 'test');
		$model = new ModelFixture();
		$model->foo = 'test';
		$model->id = 1;
		$model->markAsStored();

		$storage->purge($model);

		$this->assertEquals(
			array(
			),
			$this->getFormSql($pdo, 'select * from test')
		);
	}

	/**
	 * @test
	 * @depends storeNewModel
	 * @expectedException \Systream\Repository\Storage\Exception\DirtyModelException
	 */
	public function deleteDirtyModel()
	{
		$this->storeNewModel();
		$pdo = $this->getPDO();

		$storage = new SqlStorage($pdo, 'test');
		$model = new ModelFixture();
		$model->foo = 'test';
		$model->id = 1;
		$storage->purge($model);
	}

	/**
	 * @test
	 * @expectedException \Systream\Repository\Storage\Exception\NothingDeletedException
	 */
	public function deleteNotExistsModel()
	{
		$pdo = $this->getPDO();
		$this->createTestTable($pdo);
		$storage = new SqlStorage($pdo, 'test');
		$model = new ModelFixture();
		$model->foo = 'test';
		$model->id = 1;
		$model->markAsStored();

		$storage->purge($model);
	}

	/**
	 * @test
	 * @expectedException \Systream\Repository\Storage\Exception\NothingDeletedException
	 */
	public function purgeWithNullId()
	{
		$pdo = $this->getPDO();
		$this->createTestTable($pdo);
		$storage = new SqlStorage($pdo, 'test');
		$model = new ModelFixture();
		$model->foo = 'test';
		$model->id = null;
		$model->markAsStored();

		$storage->purge($model);
	}

	/**
	 * @param \PDO $pdo
	 * @param string $sql
	 * @param array $bind
	 * @return array
	 */
	protected function getFormSql(\PDO $pdo, $sql, array $bind = array())
	{
		$statement = $pdo->prepare($sql);
		$statement->execute($bind);
		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * @param $pdo
	 */
	protected function createTestTable($pdo)
	{
		$pdo->exec('DROP TABLE IF EXISTS `test`');
		$pdo->exec('
        	CREATE TABLE IF NOT EXISTS `test` (
            	`id` INTEGER PRIMARY KEY,
            	`foo` varchar(100) NOT NULL,
            	`bar` INT (11) null
        	)
        ');
	}
}
