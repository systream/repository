<?php

namespace Systream\Repository\Model;


use Systream\Repository\Storage\StorageInterface;

interface ActiveRecordInterface
{
	/**
	 * @return StorageInterface
	 */
	public function getStorage();

	/**
	 * @return void
	 */
	public function persist();

	/**
	 * @return void
	 */
	public function purge();

}