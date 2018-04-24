<?php

namespace App\SDD\Storage;

use App\SDD\Storage\StorageInterface;

class File implements StorageInterface
{
	const STORAGE_DIRECTORY = __DIR__ . '/../../../data/';


	public function store($token = null, $data = [])
	{
		# Create new file and save the data (in json type)
		$file = fopen(self::STORAGE_DIRECTORY . $token, 'w');
		fwrite($file, json_encode($data));
		fclose($file);
	}

	public function get($token = null): array
	{
		if (
			empty($token) || 
			!file_exists(self::STORAGE_DIRECTORY . $token)
		) {
			throw new \Exception('Not found');
		}

		# Extract data from file
		$data = file_get_contents(self::STORAGE_DIRECTORY . $token);
		return json_decode($data, true);
	}

	public function update($token = null, $data = [])
	{
		$this->store($token, $data);
	}

	public function delete($token = null)
	{
		unlink($this->getStorageDir() . $token);
	}

	public function getStorageDir(): string
	{
		return realpath(self::STORAGE_DIRECTORY) . '/';
	}
}