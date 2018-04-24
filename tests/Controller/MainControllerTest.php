<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\SDD\Storage\File;

class MainControllerTest extends WebTestCase
{
	private $client;
	private $storage;


	public function setUp()
	{
		$this->client = static::createClient();
		$this->client->disableReboot();
		$this->storage = new File;		
	}

	public function tearDown()
	{
		$this->client  = null;
		$this->storage = null;
	}

	// ------------------------------------------------------------------------
	
	public function testPostSuccess()
	{
		$this->client->request('POST', '/route', ['locations' => static::dataset()['success']]);
		$response = json_decode($this->client->getResponse()->getContent());

		$this->assertObjectHasAttribute('token', $response);
		$this->storage->delete($response->token);
	}

	public function testPostFailEmptyData()
	{
		$this->client->request('POST', '/route', ['locations' => static::dataset()['empty']]);
		$response = $this->client->getResponse()->getContent();

		$this->assertObjectHasAttribute('error', json_decode($response));
	}

	public function testPostFailInvalidData()
	{
		$this->client->request('POST', '/route', ['locations' => static::dataset()['invalid']]);
		$response = $this->client->getResponse()->getContent();

		$this->assertObjectHasAttribute('error', json_decode($response));
	}

	public function testGetSuccess()
	{
		# Should be able to return the shortest distance calculation
		$this->client->request('POST', '/route', ['locations' => static::dataset()['success']]);
		$response = json_decode($this->client->getResponse()->getContent());

		$this->client->request('GET', '/route/' . $response->token);
		$responseGet = json_decode($this->client->getResponse()->getContent());

		$attributes = ['status', 'path', 'total_distance', 'total_time'];
		foreach ($attributes as $attr) {
			$this->assertObjectHasAttribute($attr, $responseGet);	
		}
		$this->storage->delete($response->token);
	}

	private static function dataset()
	{
		return [
			'success' => [
				["22.372081", "114.107877"],
				["22.284419", "114.159510"],
				["22.326442", "114.167811"]
			],
			'invalid' => [
				["22.372081", "114.107877"]
			],
			'empty'   => []
		];
	}
}