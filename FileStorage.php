<?php

namespace cebe\jsonstore;

use Rhumsaa\Uuid\Uuid;

/**
 * Implements a dead simple storage for Json files.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class FileStorage implements Storage
{
    /**
     * @var string storage base path.
     */
    protected $storagePath;


	public function __construct($storagePath)
	{
        $this->storagePath = $storagePath;
		if (!is_dir($this->storagePath)) {
			throw new \Exception('Storage path does not exist: ' . $this->storagePath);
		}
	}

	public function insert(Data $data)
	{
        $type = $this->type($data);
		$key = $this->generateId($type);
		$sdata = $data->serialize();
        $path = $this->getPath($type, $key, time());
        mkdir(dirname($path), 0777, true); // umask applied to 0777
        file_put_contents($path, $sdata,  LOCK_EX);
        $path = $this->getPath($type, $key);
		file_put_contents($path, $sdata,  LOCK_EX);
		return $key;
	}

	public function update($key, Data $data)
	{
        $type = $this->type($data);
		$file = $this->getPath($type, $key);
		if (is_file($file)) {
            $sdata = $data->serialize();
			file_put_contents($this->getPath($type, $key), $sdata,  LOCK_EX);
			file_put_contents($this->getPath($type, $key, time()), $sdata,  LOCK_EX);
		} else {
			throw new \Exception('Can not update a non existing record.');
		}
	}

	public function delete($type, $key)
	{
		$file = $this->getPath($this->type($type), $key);
		if (is_file($file)) {
			unlink($file);
		} else {
			throw new \Exception('Can not delete a non existing record.');
		}
	}

	public function exists($type, $key)
	{
		$file = $this->getPath($this->type($type), $key);
		return is_file($file);
	}

	public function getOne($type, $key)
	{
		if (empty($key)) {
			return null;
		}
		$file = $this->getPath($this->type($type), $key);
		if (is_file($file)) {
			return $type::unserialize(file_get_contents($file));
		} else {
			return null;
		}
	}

	public function getAll($type, $condition = null)
	{
		$files = glob($this->getPathAll($this->type($type)));
		foreach($files as $file) {
			$key = basename($file, '.json');
			$record = $this->unserialize(file_get_contents($file));
			if ($this->match($record, $condition)) {
				yield $key => $record;
			}
		}
	}

    protected function match($record, $condition)
    {
        if ($condition === null) {
            return true;
        }
        if (is_array($condition)) {
            foreach($condition as $k => $v) {
                if ($record[$k] != $v) {
                    return false;
                }
            }
            return true;
        }
        if ($condition instanceof \Closure) {
            return call_user_func($condition, $record);
        }
        throw new \Exception('Invalid Condition type: ' . gettype($condition));
    }

    protected function getPathAll($type)
    {
        return "$this->storagePath/$type/*.json";
    }

    protected function getPath($type, $key, $timestamp = null)
	{
		if (!preg_match('/^[0-9a-f\-]+$/i', $key)) {
			throw new \Exception('Invalid key given.');
		}
		if ($timestamp === null) {
			$path = "$this->storagePath/$type/$key.json";
		} else {
			$path = "$this->storagePath/$type/$key/$timestamp.json";
		}
		return $path;
	}

    protected function generateId($entityType)
    {
        while($this->exists($entityType, $id = Uuid::uuid1()->getHex())) {
        }
        return $id;
    }

    /**
     * @param object $object
     * @return string string representation of the type of an object.
     */
    protected function type($object)
    {
        return strtolower(preg_replace('/[^A-z0-9]/', '-', is_object($object) ? get_class($object) : ltrim($object, '\\')));
    }
}
