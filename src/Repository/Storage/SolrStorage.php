<?php

namespace Systream\Repository\Storage;


use Solarium\Client as SolrClient;
use Systream\Repository\Model\ModelInterface;
use Systream\Repository\Model\SavableModelInterface;
use Systream\Repository\ModelList\ModelList;
use Systream\Repository\ModelList\ModelListInterface;
use Systream\Repository\Storage\Exception\CouldNotPersistException;
use Systream\Repository\Storage\Exception\CouldNotPurgeException;
use Systream\Repository\Storage\Exception\NotSupportedFilterException;
use Systream\Repository\Storage\Query\KeyValueFilter;
use Systream\Repository\Storage\Query\QueryInterface;

class SolrStorage implements StorageInterface, QueryableStorageInterface
{
	/**
	 * @var SolrClient
	 */
	private $solrClient;

	/**
	 * SolrStorage constructor.
	 * @param SolrClient $solrClient
	 */
	public function __construct(SolrClient $solrClient)
	{
		$this->solrClient = $solrClient;
	}

	/**
	 * @param SavableModelInterface $model
	 * @throws CouldNotPersistException
	 */
	public function persist(SavableModelInterface $model)
	{
		$update = $this->solrClient->createUpdate();
		$doc = $update->createDocument();
		foreach ($model->getFields() as $field) {
			$doc->$field = $model->$field;
		}
		$doc->id = $model->getId();

		$update->addDocument($doc);
		$update->addCommit();
		$result = $this->solrClient->update($update);

		if ($result->getStatus() != 0) {
			throw new CouldNotPersistException(
				'Model cannot persists: ' .  $result->getResponse()->getStatusMessage()
			);
		}
		$model->markAsStored();
	}

	/**
	 * @param SavableModelInterface $model
	 * @throws CouldNotPurgeException
	 */
	public function purge(SavableModelInterface $model)
	{
		$update = $this->solrClient->createUpdate();
		$update->addDeleteById($model->getId());
		$update->addCommit();

		$result = $this->solrClient->update($update);
		if ($result->getStatus() != 0) {
			throw new CouldNotPurgeException(
				'Model could not purge: ' . $result->getResponse()->getStatusMessage()
			);
		}
	}

	/**
	 * @param QueryInterface $query
	 * @param ModelInterface $model
	 * @return ModelListInterface
	 * @throws NotSupportedFilterException
	 */
	public function find(QueryInterface $query, ModelInterface $model)
	{
		$solrQuery = $this->solrClient->createSelect();
		$helper = $solrQuery->getHelper();

		foreach ($query->getFilters() as $filter) {
			if (!$filter instanceof KeyValueFilter) {
				throw new NotSupportedFilterException(
					sprintf('%s filter is not supported or unknown.', get_class($filter))
				);
			}
			$solrQuery->createFilterQuery($filter->getFieldName())
				->setQuery($filter->getFieldName() . ': ' . $helper->escapePhrase($filter->getValue()));
		}

		if ($query->getLimit() !== null) {
			$solrQuery->setRows($query->getLimit());
		}

		if ($query->getOffset() !== null) {
			$solrQuery->setStart($query->getOffset());
		}

		$result = $this->solrClient->select($solrQuery);

		$list = new ModelList();
		foreach ($result as $doc) {
			/** @var ModelInterface $item */
			$item = clone $model;
			$item->loadData($doc->getFields());
			if ($item instanceof SavableModelInterface) {
				$item->markAsStored();
			}
			$list->addListItem($item);
		}

		return $list;
	}
}