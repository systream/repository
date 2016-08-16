<?php

namespace Systream\Repository\Storage;


use Systream\Repository\Model\ModelInterface;
use Systream\Repository\Model\SavableModelInterface;
use Systream\Repository\ModelList\ModelList;
use Systream\Repository\ModelList\ModelListInterface;
use Systream\Repository\Storage\Exception\DirtyModelException;
use Systream\Repository\Storage\Exception\NothingDeletedException;
use Systream\Repository\Storage\Query\QueryInterface;

class NullStorage implements StorageInterface, QueryableStorageInterface
{

	/**
	 * @param SavableModelInterface $model
	 */
	public function persist(SavableModelInterface $model)
	{
		if ($model->isDirty()) {
			$model->markAsStored();
		}
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
	}

	/**
	 * @param QueryInterface $query
	 * @param ModelInterface $model
	 * @return ModelListInterface
	 */
	public function find(QueryInterface $query, ModelInterface $model)
	{
		return new ModelList();
	}
}