<?php

namespace Systream\Repository\Model;

abstract class ActiveRecordModelAbstract extends ModelAbstract implements ActiveRecordInterface
{
	/**
	 * @return void
	 */
	public function persist()
	{
		$this->getStorage()->persist($this);
	}

	/**
	 * @return void
	 */
	public function purge()
	{
		$this->getStorage()->purge($this);
	}
}