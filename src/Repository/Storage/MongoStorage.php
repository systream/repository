<?php

namespace Systream\Repository\Storage;


use MongoDB\Collection as MongoDbCollection;
use Systream\Repository\Model\ModelInterface;
use Systream\Repository\Model\SavableModelInterface;
use Systream\Repository\ModelList\ModelListInterface;
use Systream\Repository\Storage\Exception\NotSupportedFilterException;
use Systream\Repository\Storage\Query\QueryInterface;

/**
 * Class MongoStorage
 * @package Systream\Repository\Storage
 *
 * MongoStorage Bridge
 */
class MongoStorage implements StorageInterface, QueryableStorageInterface
{

	/**
	 * @var StorageInterface|QueryableStorageInterface
	 */
	private $storage;

	/**
	 * MongoStorage constructor.
	 * @param \MongoCollection|MongoDbCollection $client
	 */
	public function __construct($client)
	{
		if ($client instanceof \MongoCollection) {
			$this->storage = new MongoStoragePHP5($client);
		}

		if ($client instanceof MongoDbCollection) {
			$this->storage = new MongoStoragePHP7($client);
		}

		if (!$this->storage) {
			throw new \InvalidArgumentException('Client not supported: ' . get_class($client));
		}
	}

	/**
	 * @param SavableModelInterface $model
	 * @return void
	 */
	public function persist(SavableModelInterface $model)
	{
		$this->storage->persist($model);
	}

	/**
	 * @param SavableModelInterface $model
	 * @return void
	 */
	public function purge(SavableModelInterface $model)
	{
		$this->storage->purge($model);
	}

	/**
	 * @param QueryInterface $query
	 * @param ModelInterface $model
	 * @return ModelListInterface
	 * @throws NotSupportedFilterException
	 */
	public function find(QueryInterface $query, ModelInterface $model)
	{
		return $this->storage->find($query, $model);
	}
}