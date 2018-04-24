<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};
use App\SDD\Token;

class MainController extends Controller
{
	/**
	 * @Route("/route/{token}", methods="GET")
	 */
	public function getRoute(string $token = null, Request $request, Token $tokenManager)
	{
		$response = $tokenManager->get($token);

		# JSONP?
		if ($request->get('jsonp')) {
			$response->setCallback($request->get('jsonp'));
		}
		return new JsonResponse($response);
	}

	/**
	 * @Route("/route", methods="POST")
	 */
	public function postRoute(Request $request, Token $tokenManager)
	{
		$response = $tokenManager->create($request->get('locations'));

		# JSONP?
		if ($request->get('jsonp')) {
			$response->setCallback($request->get('jsonp'));
		}
		return new JsonResponse($response);
	}
}