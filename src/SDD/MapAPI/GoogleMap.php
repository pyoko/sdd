<?php

namespace App\SDD\MapAPI;

use App\SDD\MapAPI\MapAPIInterface;

class GoogleMap implements MapAPIInterface
{
	const GOOGLE_URL = 'https://maps.googleapis.com/maps/api/directions/json?mode=driving&key=';
	private static $key;


	public function __construct()
	{
		static::$key = $_ENV['GOOGLE_API_KEY'];
	}

	public function calculateDistance($locations)
	{
		# Let's rearrange the locations,
		# we want to have a list of arrays of locations 
		# that contains every drop-off location as the destination
		$arrangedLocations = $this->rearrangeLocations($locations);

		$result = [];
		foreach ($arrangedLocations as $location) {
			$converted = $this->convertLocationsToUrl($location);	
			$response  = $this->getCurl($converted['url']);
			$parsed    = $this->parseGoogleApiResult($response, $converted['location']);

			if (! empty($parsed)) {
				$result[] = array_merge(['status' => 'success'], $parsed);
			}
		}

		if (count($result) < 1) {
			throw new \Exception('FAILED TO CALCULATE');
		}
		
		# Now sort the distance and take the shortest one
		usort($result, function($a, $b) {
			return $a['total_distance'] <=> $b['total_distance'];
		});

		return $result[0];
	}

	public function rearrangeLocations($locations)
	{
		# Take the first element as the start location
		# then make every locations as the destination
		$startLocation = array_shift($locations);
		$tempLocations = [];
		foreach ($locations as $key => $location) {
			$tempLocs                       = $locations;
			$endLoc                         = end($tempLocs);
			
			# Swap
			$tempLocs[count($tempLocs) - 1] = $location;
			$tempLocs[$key]                 = $endLoc;

			# Prepend the start location
			array_unshift($tempLocs, $startLocation);
			$tempLocations[] 				= $tempLocs;
		}

		return $tempLocations;
	}

	public function getCurl($url)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);

		$response = curl_exec($curl);

		# error?
		if (curl_errno($curl)) {
			throw new \Exception(curl_error($curl));
		}

		curl_close($curl);

		return json_decode($response);
	}

	public function convertLocationsToUrl($locations) 
	{
		$splitted = $this->splitStartEndWaypoints($locations);

		# Join the coordinates
		$startLocation = implode(',', $splitted['start']); 	
		$endLocation   = implode(',', $splitted['end']);
		$waypoints     = implode('|', 
			array_map(function($location) {
				return implode(',', $location);
			}, $splitted['waypoints'])
		);

		$url  = "origin={$startLocation}&destination={$endLocation}";
		$url .= ! empty($locations) ? '&waypoints=optimize:true|' . $waypoints : '';

		return [
			'url'       => self::GOOGLE_URL . static::$key . '&' . $url,
			'location' => [
				'start'     => $splitted['start'],
				'end'       => $splitted['end'],
				'waypoints' => $splitted['waypoints']
			]
		];
	}

	public function parseGoogleApiResult($response, $location)
	{
		# Status not OK?
		if ($response->status !== 'OK') {
			return null;
		}

		// ------------------------------------------------------------------------
		
		# By default Google gives the shortest duration routes depending upon the current traffic conditions
		# Let's just assume the first route result is the shortest driving distance 
		# (well, since we don't request alternative routes in the request URL after all)
		# So, we just take the first route and sum all its legs' distance and duration
		$totalDistance = $totalTime = 0;
		foreach ($response->routes[0]->legs as $legs) {
			$totalDistance += $legs->distance->value;
			$totalTime     += $legs->duration->value;
		}

		# Because we use optimized waypoints setting on Google API request.
		# We need to reorder the waypoints
		$temp = [];
		if (! empty($location['waypoints'])) {
			foreach ($response->routes[0]->waypoint_order as $order) {
				$temp[] = $location['waypoints'][$order];
			}
		}

		array_unshift($temp, $location['start']);
		array_push($temp, $location['end']);
		$location = $temp;


		return [
			'path'			 => $location,
			'total_distance' => $totalDistance,
			'total_time'     => $totalTime
		];
	}

	private function splitStartEndWaypoints($locations) 
	{
		# Let's extract the first and the last location in the array
		# The rest remaining locations should be the waypoints (if available)
		$startLocation = array_splice($locations, 0, 1);
		$endLocation   = array_splice($locations, -1, 1);

		return [
			'start'     => end($startLocation),
			'end'       => end($endLocation),
			'waypoints' => $locations
		];
	}
}

