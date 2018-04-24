<?php

namespace App\Tests\MapAPI;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\SDD\MapAPI\GoogleMap;
use App\SDD\MapAPI\Permutation;

class GoogleMapAPITest extends KernelTestCase
{
	private $mapAPI;


	public function setUp()
	{
		$this->mapAPI = new GoogleMap;		
	}

	public function tearDown()
	{
		$this->mapAPI = null;
	}

	// ------------------------------------------------------------------------
	
	public function testCalculationSuccess()
	{
		$result = $this->mapAPI->calculateDistance(static::dataset()['input']);
		$this->assertSame(static::dataset()['success'], $result);
	}

	public function testGetCurlSuccess()
	{
		try {
			$this->mapAPI->getCurl(static::dataset()['urls']);
		} catch (\Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	public function testGetCurlFail()
	{
		try {
			$this->mapAPI->getCurl('http://unknown-address');

			$this->fail('Expected an Exception');
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}
	}

	public function testParserSuccess()
	{
		try {
			$response = $this->mapAPI->getCurl(static::dataset()['urls']);
			$result = ['status' => 'success'];
			$result = array_merge(
				$result, 
				$this->mapAPI->parseGoogleApiResult($response, static::dataset()['locations'])
			);

			$this->assertSame(static::dataset()['success'], $result);
		} catch (\Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	private static function dataset()
	{
		return [
			'input' => [
				["22.372081", "114.107877"],
				["22.284419", "114.159510"],
				["22.290541", "114.197076"],  
				["22.326442", "114.167811"],
			],
			'urls'  => 'https://maps.googleapis.com/maps/api/directions/json?mode=driving&key=' . $_ENV['GOOGLE_API_KEY'] . '&origin=22.372081,114.107877&destination=22.290541,114.197076&waypoints=optimize:true|22.326442,114.167811|22.284419,114.159510',
			'locations' => [
				'start'     => ["22.372081", "114.107877"],
				'end'       => ["22.290541", "114.197076"],
				'waypoints' => [
					["22.326442", "114.167811"],
					["22.284419", "114.159510"], 
				]
			],
			'success' => [
				'status' 		 => 'success',
				'path'			 => [
					["22.372081", "114.107877"], 
					["22.326442", "114.167811"],
					["22.284419", "114.159510"], 
					["22.290541", "114.197076"]
				],
				'total_distance' => 22912,
				'total_time'     => 2476
			]
		];
	}
}