<?php

namespace Systream\Repository\Model;


interface SavableModelInterface extends ModelInterface
{
	/**
	 * @return array
	 */
	public function toArray();

	/**
	 * @return void
	 */
	public function markAsStored();

	/**
	 * @return bool
	 */
	public function isDirty();

	/**
	 * @param $propertyName
	 * @return mixed
	 */
	public function getOriginalValue($propertyName);

	/**
	 * @return string|null
	 */
	public function getId();

	/**
	 * @return array
	 */
	public function getFields();
}