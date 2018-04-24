<?php

namespace App\SDD;

use App\SDD\TokenInterface;
use App\SDD\Storage\StorageInterface;
use App\SDD\MapAPI\MapAPIInterface;

class Token implements TokenInterface
{
	private $storageHandler;
	private $mapAPI;


	public function __construct(StorageInterface $storageHandler, MapAPIInterface $mapAPI)
	{
		$this->storageHandler = $storageHandler;
		$this->mapAPI         = $mapAPI;
	}

	public function create($data = []): array
	{
		# Generate new token then check whether the data is valid
		# If it's valid then ask Google for the shortest duration/distance
		# Then, store the response to the storage
		# 
		# Any error happens during the process, return the error message
		$newToken = $this->generateNewToken();
		
		try {
			$this->validate($data);

			$apiResult = $this->mapAPI->calculateDistance($data);
			$this->storageHandler->store($newToken, $apiResult);
		} catch (\Exception $e) {
			return ['error' => $e->getMessage()];
		}
		
		return ['token' => $newToken];
	}

	public function get($token = null): array
	{
		try {
			return $this->storageHandler->get($token);
		} catch (\Exception $e) {
			return [
				'status' => 'failure',
				'error'  => $e->getMessage()
			];
		}
	}

	public function remove($token = null)
	{
		$this->storageHandler->delete($token);
	}

	public function generateNewToken(): string
	{
		return bin2hex(random_bytes(20));
	}

	private function validate($data = [])
	{
		# Empty?
		if (empty($data)) {
			throw new \Exception('Empty dataset');
		}

		# Only contains one array of coordinate
		if (count($data) < 2) {
			throw new \Exception('Should have at least 1 drop-off location');	
		}

		# If not multi-dimension array
		if (!is_array($data[0])) {
			throw new \Exception('Invalid dataset');
		}
	}
}