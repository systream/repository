<?php

namespace Systream\Repository\Model;
use Systream\Repository\ModelList\ModelListInterface;

/**
 * @property string $id
 */
abstract class ModelAbstract implements ModelInterface, SavableModelInterface
{
	/**
	 * @var bool
	 */
	protected $isDirty = true;

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var array
	 */
	protected $originalData = array();

	/**
	 * @param array $data
	 * @return Void
	 */
	public function loadData(array $data)
	{
		$this->data = $data;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}

		return null;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	function __set($name, $value)
	{
		if (!isset($this->data[$name]) || $this->data[$name] != $value) {
			$this->isDirty = true;
		}
		$this->data[$name] = $value;
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		$data = [];
		foreach ($this->data as $column => $value) {
			if ($value instanceof ModelInterface) {
				$data[$column] = $value->getData();
				continue;
			}

			if ($value instanceof ModelListInterface) {
				$data[$column] = array();
				foreach ($value as $item) {
					$data[$column][] = $item->getData();
				}
				continue;
			}
			$data[$column] = $value;
		}
		return $data;
	}

	/**
	 * @return bool
	 */
	public function isDirty()
	{
		return $this->isDirty;
	}

	/**
	 * @return void
	 */
	public function markAsStored()
	{
		$this->isDirty = false;
		$this->originalData = $this->data;
	}

	/**
	 * @param string $name
	 * @return null|mixed
	 */
	public function getOriginalValue($name)
	{
		if (isset($this->originalData[$name])) {
			return $this->originalData[$name];
		}

		return null;
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		return array_keys($this->data);
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return (array)$this->getData();
	}

	/**
	 * @return string|null
	 */
	public function getId()
	{
		return $this->id;
	}

	public function validate()
	{

	}
}