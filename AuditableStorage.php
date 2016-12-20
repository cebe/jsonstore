<?php

namespace cebe\jsonstore;

/**
 * Abstract storage class
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
interface AuditableStorage extends Storage
{
	public function getHistory($type, $key);

	public function getUpdateTime($type, $key);

	public function getCreateTime($type, $key);
}
