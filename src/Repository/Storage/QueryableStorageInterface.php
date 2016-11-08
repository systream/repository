<?php

namespace Systream\Repository\Storage;


use Systream\Repository\Model\ModelInterface;
use Systream\Repository\ModelList\ModelListInterface;
use Systream\Repository\Storage\Query\QueryInterface;

interface QueryableStorageInterface extends StorageInterface
{
	/**
	 * @param QueryInterface $query
	 * @param ModelInterface $model
	 * @return ModelListInterface
	 */
	public function find(QueryInterface $query, ModelInterface $model);
}