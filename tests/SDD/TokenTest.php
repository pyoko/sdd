<?php

namespace App\Tests\SDD;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\SDD\Token;

class TokenTest extends KernelTestCase
{
	private $tokenManager;


	public function setUp()
	{
		$kernel = self::bootKernel();	
		$this->tokenManager = $kernel->getContainer()->get('test.token');
	}

	public function tearDown()
	{
		$this->tokenManager = null;
	}

	// ------------------------------------------------------------------------
	
	public function testCreateSuccess()
	{
		try {
			$token = $this->tokenManager->create(static::dataset()['input']);

			$this->assertNotEmpty($token);
		} catch (\Exception $e) {
			$this->fail($e->getMessage());
		}
		$this->tokenManager->remove($token['token']);
	}

	public function testCreateSuccessCalculation()
	{
		try {
			$token = $this->tokenManager->create(static::dataset()['input']);
			$data  = $this->tokenManager->get($token['token']);

			$this->assertSame(static::dataset()['success'], $data);
		} catch (\Exception $e) {
			$this->fail($e->getMessage());
		}
		$this->tokenManager->remove($token['token']);
	}

	public function testCreateFailEmptyData()
	{
		try {
			$token = $this->tokenManager->create();
			$this->assertSame($token, static::dataset()['failure']['empty']);
		} catch (\Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	public function testCreateFailOneDataPoint()
	{
		try {
			$token = $this->tokenManager->create([["22.372081", "114.107877"]]);
			$this->assertSame($token, static::dataset()['failure']['one_location']);
		} catch (\Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	public function testCreateFailNotMultiDimensionArray()
	{
		try {
			$token = $this->tokenManager->create(["22.372081", "114.107877"]);
			$this->assertSame($token, static::dataset()['failure']['invalid']);
		} catch (\Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	private static function dataset()
	{
		return [
			'input' => [
				["22.372081", "114.107877"],
				["22.326442", "114.167811"],
				["22.284419", "114.159510"]
			],
			'success' => [
				'status'         => 'success',
				'path'           => [
					["22.372081", "114.107877"],
					["22.326442", "114.167811"],
					["22.284419", "114.159510"]
				],
				'total_distance' => 18153,
				'total_time'     => 1774
			],
			'progress' => [
				'status' 		 => 'in progress'
			],
			'failure'  => [
				'invalid'      => [
					'error'      => 'Invalid dataset'
				],
				'one_location' => [
					'error'      => 'Should have at least 1 drop-off location'
				],
				'empty'        => [
					'error'      => 'Empty dataset'
				]
			]
		];
	}
}