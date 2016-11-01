<?php

namespace Systream\Repository\Storage;


use MongoDB\Collection;
use Systream\Repository\Model\ModelInterface;
use Systream\Repository\Model\SavableModelInterface;
use Systream\Repository\ModelList\ModelList;
use Systream\Repository\ModelList\ModelListInterface;
use Systream\Repository\Storage\Exception\NotSupportedFilterException;
use Systream\Repository\Storage\Query\KeyValueFilter;
use Systream\Repository\Storage\Query\QueryInterface;

class MongoStoragePHP7 implements StorageInterface, QueryableStorageInterface
{
	/**
	 * @var Collection
	 */
	private $collection;

	/**
	 * @var array
	 */
	protected static $supportedFilters = array(
		KeyValueFilter::class
	);

	/**
	 * MongoStorage constructor.
	 * @param Collection $collection
	 */
	public function __construct(Collection $collection)
	{
		$this->collection = $collection;
	}

	/**
	 * @param SavableModelInterface $model
	 * @return void
	 */
	public function persist(SavableModelInterface $model)
	{
		$this->collection->insertOne($model->toArray());
		$model->markAsStored();
	}

	/**
	 * @param SavableModelInterface $model
	 * @return void
	 */
	public function purge(SavableModelInterface $model)
	{
		$this->collection->deleteOne(array('id' => $model->getId()));
		$model->markAsStored();
	}

	/**
	 * @param QueryInterface $query
	 * @param ModelInterface $model
	 * @return ModelListInterface
	 * @throws NotSupportedFilterException
	 */
	public function find(QueryInterface $query, ModelInterface $model)
	{
		$queryArray = array();
		foreach ($query->getFilters() as $filter) {
			if (!in_array(get_class($filter), self::$supportedFilters)) {
				throw new NotSupportedFilterException(
					sprintf('%s filter is not supported or unknown.', get_class($filter))
				);
			}
			$queryArray[$filter->getFieldName()] = $filter->getValue();
		}

		$options = array();

		if ($query->getLimit() !== null) {
			$options['limit'] = $query->getLimit();
		}

		if ($query->getOffset() !== null) {
			$options['skip'] = $query->getOffset();
		}

		$list = new ModelList();
		$cursor = $this->collection->find($queryArray, $options);

		/** @var \MongoDB\Model\BSONDocument $doc */
		foreach ($cursor as $doc) {
			$docArray = (array) $doc;
			unset($docArray['_id']);
			/** @var ModelInterface $item */
			$item = new $model();
			$item->loadData($docArray);
			if ($item instanceof SavableModelInterface) {
				$item->markAsStored();
			}
			$list->addListItem($item);
		}

		return $list;
	}
}