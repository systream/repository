<?php

namespace Systream\Repository\Model;


interface ModelInterface
{
	/**
	 * @param array $data
	 * @return void
	 */
	public function loadData(array $data);

	/**
	 * @return array
	 */
	public function getData();

	/**
	 * @return array
	 */
	public function getFields();


}