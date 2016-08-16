<?php

namespace Tests\Systream\Storage;


use Systream\Repository\Storage\Query\KeyValueFilter;
use Systream\Repository\Storage\Query\Query;
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
	 * @test
	 */
	public function transaction()
	{
		$pdo = $this->getPDO();
		$this->createTestTable($pdo);
		$storage = new SqlStorage($pdo, 'test');
		$model1 = new ModelFixture();
		$model1->foo = 'test';

		$model2 = new ModelFixture();
		$model2->foo = 'bar';

		$storage->beginTransaction();
		$storage->persist($model1);
		$storage->persist($model2);
		$storage->commit();
	}

	/**
	 * @test
	 */
	public function transactionRollback()
	{
		$pdo = $this->getPDO();
		$this->createTestTable($pdo);
		$storage = new SqlStorage($pdo, 'test');
		$model1 = new ModelFixture();
		$model1->foo = 'test';

		$model2 = new ModelFixture();
		$model2->foo = 'bar';

		$storage->beginTransaction();
		$storage->persist($model1);
		$storage->persist($model2);
		$storage->rollBack();
	}

	/**
	 * @test
	 */
	public function find()
	{
		$pdo = $this->getPDO();
		$this->createTestTable($pdo);
		$storage = new SqlStorage($pdo, 'test');

		$model1 = new ModelFixture();
		$model1->foo = 'test';
		$model1->bar = 2;

		$model2 = new ModelFixture();
		$model2->foo = 'bar';
		$model2->bar = 5;
		$storage->persist($model1);
		$storage->persist($model2);

		$model = new ModelFixture();
		$query = new Query();
		$query->addFilter(KeyValueFilter::create('foo', 'bar'));
		$modelList = $storage->find($query, $model);

		$this->assertFalse($modelList->isEmpty());
		foreach ($modelList as $item) {
			$this->assertEquals($model2, $item);
		}
	}

	/**
	 * @test
	 */
	public function find_multiQuery()
	{
		$pdo = $this->getPDO();
		$this->createTestTable($pdo);
		$storage = new SqlStorage($pdo, 'test');

		$model1 = new ModelFixture();
		$model1->foo = 'test';
		$model1->bar = 2;

		$model2 = new ModelFixture();
		$model2->foo = 'bar';
		$model2->bar = 5;
		$storage->persist($model1);
		$storage->persist($model2);

		$model = new ModelFixture();
		$query = new Query();
		$query->addFilter(KeyValueFilter::create('foo', 'bar'));
		$query->addFilter(KeyValueFilter::create('bar', 5));
		$modelList = $storage->find($query, $model);

		$this->assertFalse($modelList->isEmpty());
		foreach ($modelList as $item) {
			$this->assertEquals($model2, $item);
		}
	}

	/**
	 * @test
	 */
	public function find_multiQueryNotFound()
	{
		$pdo = $this->getPDO();
		$this->createTestTable($pdo);
		$storage = new SqlStorage($pdo, 'test');

		$model1 = new ModelFixture();
		$model1->foo = 'test';
		$model1->bar = 2;

		$model2 = new ModelFixture();
		$model2->foo = 'bar';
		$model2->bar = 5;
		$storage->persist($model1);
		$storage->persist($model2);

		$model = new ModelFixture();
		$query = new Query();
		$query->addFilter(KeyValueFilter::create('foo', 'bar'));
		$query->addFilter(KeyValueFilter::create('bar', 2));
		$modelList = $storage->find($query, $model);
		$this->assertTrue($modelList->isEmpty());
	}

	/**
	 * @test
	 */
	public function find_emptyQuery()
	{
		$pdo = $this->getPDO();
		$this->createTestTable($pdo);
		$storage = new SqlStorage($pdo, 'test');

		$model1 = new ModelFixture();
		$model1->foo = 'test';
		$model1->bar = 2;

		$model2 = new ModelFixture();
		$model2->foo = 'bar';
		$model2->bar = 5;
		$storage->persist($model1);
		$storage->persist($model2);

		$model = new ModelFixture();
		$query = new Query();
		$modelList = $storage->find($query, $model);
		$this->assertFalse($modelList->isEmpty());
		$this->assertEquals(2, $modelList->count());
	}

	/**
	 * @test
	 */
	public function find_Performance()
	{
		$pdo = $this->getPDO();
		$this->createTestTable($pdo);
		$storage = new SqlStorage($pdo, 'test');

		$count = 10000;
		$countIterator = $count;
		while ($countIterator) {
			$model1 = new ModelFixture();
			$model1->foo = md5(microtime(true));
			$model1->bar = rand(0, $count);

			$storage->persist($model1);
			$countIterator--;
		}

		$model = new ModelFixture();
		$query = new Query();
		$start = microtime(true);
		$modelList = $storage->find($query, $model);
		$this->assertLessThanOrEqual(0.5, (microtime(true) - $start));

		$this->assertEquals($count, $modelList->count());
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
