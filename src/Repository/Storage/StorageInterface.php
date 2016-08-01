<?php

namespace Systream\Repository\Storage;

use Systream\Repository\Model\SavableModelInterface;

interface StorageInterface
{
	/**
	 * @param SavableModelInterface $model
	 * @return void
	 */
	public function persist(SavableModelInterface $model);

	/**
	 * @param SavableModelInterface $model
	 * @return void
	 */
	public function purge(SavableModelInterface $model);

}