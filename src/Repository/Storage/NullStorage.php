<?php

namespace Systream\Repository\Storage;


use Systream\Repository\Model\SavableModelInterface;
use Systream\Repository\Storage\Exception\DirtyModelException;
use Systream\Repository\Storage\Exception\NothingDeletedException;

class NullStorage implements StorageInterface
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
}