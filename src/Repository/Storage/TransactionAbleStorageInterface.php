<?php

namespace Systream\Repository\Storage;

interface TransactionAbleStorageInterface
{
	/**
	 * @return void
	 */
	public function beginTransaction();

	/**
	 * @return void
	 */
	public function rollBack();

	/**
	 * @return void
	 */
	public function commit();
}