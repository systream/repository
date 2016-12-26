<?php

namespace Systream\Repository\Storage;


use Systream\Repository\Model\ModelInterface;
use Systream\Repository\Model\SavableModelInterface;
use Systream\Repository\ModelList\ModelList;
use Systream\Repository\ModelList\ModelListInterface;
use Systream\Repository\Storage\Exception\DirtyModelException;
use Systream\Repository\Storage\Exception\NothingDeletedException;
use Systream\Repository\Storage\Exception\NotSupportedFilterException;
use Systream\Repository\Storage\Query\KeyValueFilter;
use Systream\Repository\Storage\Query\QueryInterface;
use Systream\Repository\Storage\Query\RawSqlQuery;

class SqlStorage implements StorageInterface, TransactionAbleStorageInterface, QueryableStorageInterface
{
	/**
	 * @var \PDO
	 */
	protected $pdo;

	/**
	 * @var
	 */
	protected $table;

	/**
	 * @var array
	 */
	protected static $supportedFilters = array(
		KeyValueFilter::class
	);

	/**
	 * SqlStorage constructor.
	 * @param \PDO $pdo
	 * @param string $table
	 */
	public function __construct(\PDO $pdo, $table)
	{
		$this->pdo = $pdo;
		$this->table = $table;
	}

	/**
	 * @param SavableModelInterface $model
	 */
	public function persist(SavableModelInterface $model)
	{
		if (!$model->isDirty()) {
			return;
		}

		if ($model->getId()) {
			$this->update($model);
			return;
		}

		$this->create($model);
	}

	/**
	 * @param SavableModelInterface $model
	 * @return string
	 */
	protected function create(SavableModelInterface $model)
	{
		$bindData = array();
		$index = 0;
		$data = $model->toArray();
		$fields = $model->getFields();
		foreach ($fields as $field) {
			$bindData['data' . $index++] = $data[$field];
		}

		$keys = implode(', ', $fields);

		$query = $this->pdo->prepare(
			'insert into ' . $this->table . ' (' . $keys . ') values (:' . implode(', :',array_keys($bindData)) . ')'
		);

		$query->execute($bindData);
		$model->id = $this->pdo->lastInsertId();
		$query->closeCursor();
		$model->markAsStored();
	}

	/**
	 * @param SavableModelInterface $model
	 * @return string
	 */
	public function update(SavableModelInterface $model)
	{
		$bindData = array();

		$index = 0;
		$update = '';
		$data = $model->toArray();
		$fields = $model->getFields();
		foreach ($fields as $field) {
			$bindKey = 'data' . $index++;
			$bindData[$bindKey] = $data[$field];
			$update .= $field . ' = :' . $bindKey. ', ';
		}

		$update = substr($update, 0, -2);

		$bindData['whereId'] = $model->getId();

		$query = $this->pdo->prepare(
			'update ' . $this->table . ' set ' . $update . ' where id = :whereId '
		);

		$query->execute($bindData);
		$query->closeCursor();
		$model->markAsStored();
	}


	/**
	 * @param SavableModelInterface $model
	 * @throws DirtyModelException
	 * @throws NothingDeletedException
	 */
	public function purge(SavableModelInterface $model)
	{
		if ($model->isDirty()) {
			throw new DirtyModelException('Dirty model cannot be purged.');
		}

		$bindData = array(
			'whereId' => $model->getId()
		);

		$query = $this->pdo->prepare('delete from ' . $this->table . ' where id = :whereId');
		$query->execute($bindData);
		$affectedRowCount = $query->rowCount();
		$query->closeCursor();
		if (!$affectedRowCount) {
			throw new NothingDeletedException(sprintf('Noting deleted from %s with %s id', $this->table, $model->getId()));
		}
	}

	/**
	 * @return void
	 */
	public function beginTransaction()
	{
		$this->pdo->beginTransaction();
	}

	/**
	 * @return void
	 */
	public function rollBack()
	{
		$this->pdo->rollBack();
	}

	/**
	 * @return void
	 */
	public function commit()
	{
		$this->pdo->commit();
	}

	/**
	 * @param QueryInterface $query
	 * @param ModelInterface $model
	 * @return ModelListInterface
	 * @throws NotSupportedFilterException
	 */
	public function find(QueryInterface $query, ModelInterface $model)
	{
		$bindData = array();
		$sql = $this->getSqlFindQuery($query, $bindData);

		$query = $this->pdo->prepare($sql);
		$query->execute($bindData);

		$list = new ModelList();
		while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
			/** @var ModelInterface $item */
			$item = clone $model;
			$item->loadData($row);
			if ($item instanceof SavableModelInterface) {
				$item->markAsStored();
			}
			$list->addListItem($item);
		}

		return $list;
	}

	/**
	 * @param QueryInterface $query
	 * @param string $sql
	 * @return string
	 */
	protected function setLimit(QueryInterface $query, &$sql)
	{
		if ($query->getLimit() !== null) {

			$sql .= ' limit ';
			if ($query->getOffset() !== null) {
				$sql .= $query->getOffset() . ',';
			}
			$sql .= $query->getLimit();
		}
	}

	/**
	 * @param QueryInterface $query
	 * @param array $bindData
	 * @return string
	 * @throws NotSupportedFilterException
	 */
	private function getSqlFromQuery(QueryInterface $query, array &$bindData)
	{
		$sql = '1=1 AND ';
		$index = 0;
		foreach ($query->getFilters() as $filter) {
			$filterClassType = get_class($filter);
			if (!in_array($filterClassType, self::$supportedFilters)) {
				throw new NotSupportedFilterException(
					sprintf('%s filter is not supported or unknown.', get_class($filter))
				);
			}

			$value = $filter->getValue();

			if ($filterClassType === RawSqlQuery::class) {
				$sql .= $value . ' AND ';
			} else {

				if ($value === null) {
					$sql .= $filter->getFieldName() . ' is null AND ';
					continue;
				}

				$bindKey = 'where_data' . $index++;

				$bindData[$bindKey] = $value;
				$sql .= $filter->getFieldName() . ' = :' . $bindKey . ' AND ';
			}
		}

		$sql = substr($sql, 0, -5);
		$this->setLimit($query, $sql);

		return 'select * from ' . $this->table . ' where ' . $sql;
	}

	/**
	 * @param QueryInterface $query
	 * @param array $bindData
	 * @return string
	 */
	private function getSqlFindQuery(QueryInterface $query, array &$bindData)
	{
		if ($query instanceof RawSqlQuery) {
			$sql = $query->getSql();
			$bindData = $query->getBindData();
			return $sql;
		}

		return $this->getSqlFromQuery($query, $bindData);
	}
}