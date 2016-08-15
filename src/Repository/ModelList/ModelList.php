<?php

namespace Systream\Repository\ModelList;


use Systream\Repository\Model\ModelInterface;

class ModelList implements ModelListInterface
{
	/**
	 * @var int
	 */
	private $cursor = 0;

	/**
	 * @var array
	 */
	private $items = array();

	/**
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current()
	{
		if ($this->valid()) {
			return $this->items[$this->cursor];
		}
		return null;
	}

	/**
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key()
	{
		return $this->cursor;
	}

	/**
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid()
	{
		return isset($this->items[$this->cursor]);
	}

	/**
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind()
	{
		$this->reset();
	}

	/**
	 * @param ModelInterface $model
	 * @return void
	 */
	public function addListItem(ModelInterface $model)
	{
		$this->items[] = $model;
	}

	/**
	 * Set pointer to the next item
	 *
	 * @return void
	 */
	public function next()
	{
		$this->cursor++;
	}

	/**
	 * Reset pointer to the first element
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->cursor = 0;
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->items);
	}

	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		return empty($this->items);
	}
}