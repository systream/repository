<?php

namespace Systream\Repository\Storage;


use Systream\Repository\Model\SavableModelInterface;
use Systream\Repository\Storage\Exception\DirtyModelException;
use Systream\Repository\Storage\Exception\NothingDeletedException;

class SqlStorage implements StorageInterface
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
}