<?php

namespace Tests\Systream\Unit\Repository\Model;


use Systream\Repository\Model\ModelAbstract;

/**
 * @property string foo
 * @property string bar
 */
class ModelFixture extends ModelAbstract
{
	public $propertyNotSet;
}