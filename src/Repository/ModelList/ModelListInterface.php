<?php

namespace Systream\Repository\ModelList;


use Systream\Repository\Model\ModelInterface;

interface ModelListInterface extends \Iterator, \Countable
{
	/**
	 * @param ModelInterface $model
	 * @return void
	 */
	public function addListItem(ModelInterface $model);

	/**
	 * @return ModelInterface|null
	 */
	public function current();

	/**
	 * Set pointer to the next item
	 *
	 * @return void
	 */
	public function next();

	/**
	 * Reset pointer to the first element
	 *
	 * @return void
	 */
	public function reset();

	/**
	 * @return int
	 */
	public function count();

	/**
	 * @return bool
	 */
	public function isEmpty();
}