<?php

namespace Systream\Repository\Model;


interface SavableModelInterface
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
}