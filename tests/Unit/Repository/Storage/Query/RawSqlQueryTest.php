<?php

namespace Tests\Systream\Unit\Repository\Storage\Query;


use Systream\Repository\Storage\Query\Query;
use Systream\Repository\Storage\Query\RawSqlQuery;
use Systream\Repository\Storage\SqlStorage;
use Tests\Systream\Unit\Repository\Model\ModelFixture;

class RawSqlQueryTest extends \PHPUnit_Framework_TestCase
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
	 * @dataProvider rawQueryDataProvider
	 * @param string $query
	 * @param int $resultCount
	 */
	public function testRawQuery($query, $resultCount)
	{
		$pdo = $this->getPDO();

		$this->createTestTable($pdo);

		$storage = new SqlStorage($pdo, 'test');
		$model = new ModelFixture();
		$model->foo = 'test';
		$model->bar = 'bar';
		$storage->persist($model);

		$model2 = new ModelFixture();
		$model2->foo = 'test2';
		$model2->bar = 'foo';
		$storage->persist($model2);

		$model3 = new ModelFixture();
		$model3->foo = 'bar';
		$model3->bar = 'foo';
		$storage->persist($model3);

		$result = $storage->find(new RawSqlQuery($query), $model);
		$this->assertCount($resultCount, $result);
	}

	/**
	 * @test
	 */
	public function limitNull()
	{
		$query = new RawSqlQuery('select * from test');
		$this->assertNull($query->getLimit());
	}

	/**
	 * @test
	 */
	public function offsetNull()
	{
		$query = new RawSqlQuery('select * from test');
		$this->assertNull($query->getOffset());
	}

	/**
	 * @return array
	 */
	public function rawQueryDataProvider()
	{
		return array(
			array('select * from test', 3),
			array('select * from test limit 1', 1),
			array('select * from test limit 2', 2),
			array('select * from test limit 1,2', 2),
			array('select foo from test', 3),
			array('select foo from test group by bar', 2),
			array('select foo from test group by bar order by foo', 2),
			array('select foo from test group by bar order by foo DESC', 2),
		);
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