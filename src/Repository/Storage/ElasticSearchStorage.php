<?php

namespace Systream\Repository\Storage;

use Elasticsearch\Client;
use Systream\Repository\Model\ModelInterface;
use Systream\Repository\Model\SavableModelInterface;
use Systream\Repository\ModelList\ModelList;
use Systream\Repository\ModelList\ModelListInterface;
use Systream\Repository\Storage\Exception\CouldNotPersistException;
use Systream\Repository\Storage\Exception\CouldNotPurgeException;
use Systream\Repository\Storage\Exception\NotSupportedFilterException;
use Systream\Repository\Storage\Query\KeyValueFilter;
use Systream\Repository\Storage\Query\QueryInterface;

class ElasticSearchStorage implements StorageInterface, QueryableStorageInterface
{
	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * @var string
	 */
	protected $index;

	/**
	 * @var string
	 */
	protected $type;

	public function __construct(Client $client, $index, $type)
	{
		$this->client = $client;
		$this->index = $index;
		$this->type = $type;
	}

	/**
	 * @param SavableModelInterface $model
	 * @throws CouldNotPersistException
	 */
	public function persist(SavableModelInterface $model)
	{
		$params = [
			'index' => $this->index,
			'type' => $this->type,
		];

		if ($model->getId()) {

			$params['body'] = ['doc' => $model->toArray()];
			$params['id'] = $model->getId();
			$this->client->update($params);
			$model->markAsStored();
			return;
		}

		$params['body'] = $model->toArray();
		$response = $this->client->index($params);
		$model->id = $response['_id'];
		$model->markAsStored();
	}

	/**
	 * @param SavableModelInterface $model
	 * @throws CouldNotPurgeException
	 */
	public function purge(SavableModelInterface $model)
	{
		$params = [
			'index' => $this->index,
			'type' => $this->type,
			'id' => $model->getId()
		];

		$this->client->delete($params);
	}

	/**
	 * @param QueryInterface $query
	 * @param ModelInterface $model
	 * @return ModelListInterface
	 * @throws NotSupportedFilterException
	 */
	public function find(QueryInterface $query, ModelInterface $model)
	{
		$params = [
			'index' => $this->index,
			'type' => $this->type,
			'body' => [
			]
		];
		$queryArray = [];

		foreach ($query->getFilters() as $filter) {

			if (!$filter instanceof KeyValueFilter) {
				throw new NotSupportedFilterException(
					sprintf('%s filter is not supported or unknown.', get_class($filter))
				);
			}
			if (!isset($queryArray['match'])) {
				$queryArray['match'] = [];
			}

			$queryArray['match'][$filter->getFieldName()] = $filter->getValue();
		}

		if (!empty($queryArray)) {
			$params['body'] = [
				'query' => $queryArray
			];
		}


		if ($query->getLimit()) {
			$params["size"] = $query->getLimit();
		}

		if ($query->getOffset()) {
			$params['from'] = $query->getOffset();
		}

		$results = $this->client->search($params);

		$list = new ModelList();
		foreach ($results['hits']['hits'] as $doc) {
			/** @var ModelInterface $item */
			$doc['_source']['id'] = $doc['_id'];
			$item = new $model();
			$item->loadData($doc['_source']);
			if ($item instanceof SavableModelInterface) {
				$item->markAsStored();
			}
			$list->addListItem($item);
		}

		return $list;
	}
}