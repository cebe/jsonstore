<?php

namespace cebe\jsonstore;

use Rhumsaa\Uuid\Uuid;
use yii\helpers\Json;

/**
 * Abstract storage class
 *
 * Defines a dead simple storage interface.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
interface Storage
{
	public function insert(Data $data);

	public function update($key, Data $data);

	public function delete($type, $key);

	public function exists($type, $key);

	public function getOne($type, $key);

	public function getAll($type, $condition = null);
}
