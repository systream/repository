<?php

namespace Systream\Repository\Storage;


use Systream\Repository\Model\SavableModelInterface;

class MongoStorage implements StorageInterface
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
}