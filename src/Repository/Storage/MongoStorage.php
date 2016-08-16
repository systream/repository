<?php

namespace Systream\Repository\Storage;


use Systream\Repository\Model\ModelInterface;
use Systream\Repository\Model\SavableModelInterface;
use Systream\Repository\ModelList\ModelList;
use Systream\Repository\ModelList\ModelListInterface;
use Systream\Repository\Storage\Query\QueryInterface;

class MongoStorage implements StorageInterface, QueryableStorageInterface
{
	/**
	 * @var \MongoCollection
	 */
	private $collection;

	/**
	 * MongoStorage constructor.
	 * @param \MongoCollection $collection
	 */
	public function __construct(\MongoCollection $collection)
	{
		$this->collection = $collection;
	}

	/**
	 * @param SavableModelInterface $model
	 * @return void
	 */
	public function persist(SavableModelInterface $model)
	{
		$this->collection->save($model->toArray());
		$model->markAsStored();
	}

	/**
	 * @param SavableModelInterface $model
	 * @return void
	 */
	public function purge(SavableModelInterface $model)
	{
		$this->collection->remove(array('id' => $model->getId()));
		$model->markAsStored();
	}

	/**
	 * @param QueryInterface $query
	 * @param ModelInterface $model
	 * @return ModelListInterface
	 */
	public function find(QueryInterface $query, ModelInterface $model)
	{
		$queryArray = array();
		foreach ($query->getFilters() as $filter) {
			$queryArray[$filter->getFieldName()] = $filter->getValue();
		}

		$list = new ModelList();
		$cursor = $this->collection->find($queryArray);

		if ($query->getLimit() !== null) {
			$cursor->limit($query->getLimit());
		}

		if ($query->getOffset() !== null) {
			$cursor->skip($query->getOffset());
		}

		foreach ($cursor as $doc) {
			unset($doc['_id']);
			/** @var ModelInterface $item */
			$item = new $model();
			$item->loadData($doc);
			if ($item instanceof SavableModelInterface) {
				$item->markAsStored();
			}
			$list->addListItem($item);
		}

		return $list;
	}
}