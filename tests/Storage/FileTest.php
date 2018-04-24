<?php

namespace App\Tests\Storage;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\SDD\Storage\File;

class FileTest extends KernelTestCase
{
	private $storage;


	public function setUp()
	{
		$this->storage = new File;		
	}

	public function tearDown()
	{
		$this->storage = null;
	}

	// ------------------------------------------------------------------------
	
	public function testStoreSuccess()
	{
		$filename = bin2hex(random_bytes(20));	# create random string

		try {
			$this->storage->store($filename, static::dataset()['success']);

			$this->assertFileExists($this->storage->getStorageDir() . $filename);
			$this->storage->delete($filename);
		} catch (\Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	public function testUpdateSuccess()
	{
		$filename = bin2hex(random_bytes(20));	# create random string

		try {
			$this->storage->store($filename, static::dataset()['progress']);
			$data = $this->storage->get($filename);
			$this->assertSame($data, static::dataset()['progress']);


			$this->storage->update($filename, static::dataset()['success']);
			$data = $this->storage->get($filename);
			$this->assertSame($data, static::dataset()['success']);

			$this->storage->delete($filename);
		} catch (\Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	public function testGetSuccess()
	{
		$filename = bin2hex(random_bytes(20));
		$this->storage->store($filename, static::dataset()['success']);
		
		try {
			$data = $this->storage->get($filename);

			$this->assertSame($data, static::dataset()['success']);
			$this->storage->delete($filename);
		} catch (\Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	public function testGetFailNoToken()
	{
		try {
			$this->storage->get();

			$this->fail('Expected an Exception');
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}

		try {
			$this->storage->get('');
			
			$this->fail('Expected an Exception');
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}

		try {
			$this->storage->get(false);
			
			$this->fail('Expected an Exception');
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}
	}

	private static function dataset()
	{
		return [
			'success' => [
				'status'         => 'success',
				'path'           => [
					["22.372081", "114.107877"],
					["22.326442", "114.167811"],
					["22.284419", "114.159510"]
				],
				'total_distance' => 20000,
				'total_time'     => 1800
			],
			'progress' => [
				'status' 		 => 'in progress'
			],
			'failure'  => [
				'status' 		 => 'ERROR DESCRIPTION'
			]
		];
	}
}